# 支付宝第三方应用授权（app_auth_token）支持 · 技术设计文档

> **时间**：2026-09-09
> **作者**：GLM-5.3 + yansongda
> **状态**：经人工审核确认

对应 Issue：[#1091](https://github.com/yansongda/pay/issues/1091)（支付宝的第三方应用授权 token：app_auth_token 获取问题）

## 1. 背景与问题

**现状**：yansongda/pay 对支付宝 `app_auth_token` 只有「使用侧」支持——

- 全局配置：`AlipayConfig::$appAuthToken`（`src/Config/AlipayConfig.php`）
- 单次覆盖：`$params['_app_auth_token']`，V2 由 `StartPlugin::getAppAuthToken()` 写入公共参数，V3 由 `AddPayloadSignaturePlugin` → `AddRadarPlugin` 写入请求头 `alipay-app-auth-token`
- 注：现有 V2 用户文档 `web/docs/v2/alipay/index.md` 仅有「服务商模式」小节（mode=service + pid），**无** `app_auth_token` 专项用法说明，由本次新增文档补齐

**「获取侧」完全缺失**：ISV/代理商模式必须先完成「商户授权 → 回调 `app_auth_code` → 换取 `app_auth_token`」链路，而代码中无 `alipay.open.auth.token.app`（换取/刷新）与 `alipay.open.auth.token.app.query`（查询）的任何实现。

**困境**：

1. ISV 被迫手写网关请求、自行处理签名/验签/证书，才能换取授权令牌
2. 已有 `Member/Authorization/TokenPlugin`（`alipay.system.oauth.token`，用户级令牌）与 `QueryPlugin`（`alipay.open.auth.userauth.relationship.query`）容易与「应用级 token.app 接口」混淆，命名空间需要区分

**目标**（约束条件）：

- **零新依赖**、零特殊分支：完全复用现有 V2 插件管道
- **纯增量**：不改任何现有类/配置/行为（含 `Alipay::__call` 的 `strtolower` 转换链）
- **token 生命周期由业务方负责**：SDK 只提供「发起换取/刷新/查询请求」一层，不做存储/自动刷新
- **授权链接不提供 helper**：仅以用户文档说明 URL 拼接规则

## 2. 整体方案

**核心思路**：新增 `Open/Authorization` 插件族 + 单一 `TokenAppShortcut`（`_action` 分发换取/查询），响应链零改动。

```
用户代码
   │ Pay::alipay()->token_app(['grant_type'=>'authorization_code','code'=>$code])
   ▼
TokenAppShortcut (Shortcut/Alipay/)
   │ _action=default → 换取/刷新      _action=query → 查询授权信息
   ▼
StartPlugin → TokenAppPlugin|TokenAppQueryPlugin → FormatPayloadBizContentPlugin
   → AddPayloadSignaturePlugin → AddRadarPlugin → VerifySignaturePlugin
   → ResponsePlugin → ParserPlugin        ← 现有 V2 管道，零改动
   ▼
Collection(app_auth_token / app_refresh_token / auth_app_id / user_id / 有效期)
```

**文件结构**（新增 `Open/` 目录，PSR-4 自动加载无需注册）：

```
src/
├── Plugin/Alipay/V2/
│   ├── Member/Authorization/        # 现有：用户级 oauth.token / userauth.*
│   └── Open/Authorization/          # ★ 新增：应用级授权（token.app）
│       ├── TokenAppPlugin.php       # alipay.open.auth.token.app
│       └── TokenAppQueryPlugin.php  # alipay.open.auth.token.app.query
├── Shortcut/Alipay/
│   └── TokenAppShortcut.php         # ★ 新增
└── Provider/Alipay.php              # 修改：补 @method 注解
tests/                               # 镜像新增 3 个测试
web/docs/v2/alipay/authorization.md  # ★ 新增用户文档
web/.vitepress/sidebar/v2.js         # 修改：补侧边栏项
CHANGELOG.md                         # 修改：Unreleased Added
```

## 3. 详细设计

### 3.1 契约（接口对接）

**请求契约**（`alipay.open.auth.token.app`，V2 网关 `POST /gateway.do`，参数置于 biz_content）——**已验证（读过官方 SDK 模型 `alipay-sdk-php-all` 的 `v3/src/Model/AlipayOpenAuthTokenAppModel.php`，attributeMap 映射 `code`/`grant_type`/`refresh_token`；V2 `v2/aop/request/AlipayOpenAuthTokenAppRequest.php` 为 biz_content 直通）**：

| 字段 | 类型 | 说明 |
|---|---|---|
| `grant_type` | string（必填） | `authorization_code`（换取）/ `refresh_token`（刷新） |
| `code` | string | 应用授权码（app_auth_code），换取时必填，一次性、24h 有效 |
| `refresh_token` | string | 刷新时必填，来源为换取响应中的 `app_refresh_token` |

**请求契约**（`alipay.open.auth.token.app.query`）——**已验证（官方 V3 API 文档 `query($appAuthToken)`：`GET /v3/alipay/open/auth/token/app/query`，参数名 `app_auth_token`）**：V2 下该参数置于 biz_content。

**响应契约**（包裹键 `alipay_open_auth_token_app_response` / `alipay_open_auth_token_app_query_response`）：

- 包裹键由 `ResponsePlugin` 按 `str_replace('.', '_', method) . '_response'` 自动定位，**已验证（读过源码，无 method 白名单）**
- 业务字段：`app_auth_token`、`app_refresh_token`、`auth_app_id`（授权商户 app_id）、`user_id`（授权商户 user_id）、批量场景 `tokens[]`（子元素同上，官方注释「批量授权换码访问令牌列表」）、有效期字段
- ⚠️ **有效期字段语义（官方口径，已验证官方模型 `setExpiresIn` 注释）**：`expires_in` **已作废**——「该字段已作废，应用令牌长期有效，接入方不需要消费该字段」；`re_expires_in` 为「刷新令牌的有效时间（从接口调用时间作为起始时间），单位到秒」，即**以 `re_expires_in` 管理 `app_refresh_token` 的刷新窗口**，而非按有效期刷新 `app_auth_token` 本身
- ⚠️ **字段名存在双源歧义**：官方 OpenAPI 生成模型（`AlipayOpenAuthTokenAppResponseModel`，attributeMap，OpenAPI 文档版本 2026-08-26）为 `expires_in`/`re_expires_in`；部分历史文档页为 `app_auth_token_expires_in`/`app_refresh_token_expires_in`。**SDK 透传响应不映射任何业务字段**，用户文档示例标注「以实际响应为准」，不阻塞实现

curl 示例（换取）：

```bash
curl -s https://openapi.alipay.com/gateway.do?charset=utf-8 \
  -d method=alipay.open.auth.token.app -d app_id=ISV应用APPID \
  -d charset=utf-8 -d sign_type=RSA2 -d sign=... -d timestamp=... \
  -d app_cert_sn=... -d alipay_root_cert_sn=... \
  --data-urlencode 'biz_content={"grant_type":"authorization_code","code":"1a2b3c"}'
```

成功响应示例（字段名以实际响应为准）：

```json
{
  "alipay_open_auth_token_app_response": {
    "code": "10000",
    "msg": "Success",
    "app_auth_token": "xxx",
    "expires_in": "31536000",
    "app_refresh_token": "yyy",
    "re_expires_in": "32140800",
    "auth_app_id": "2018xxx",
    "user_id": "2088xxx",
    "sign": "..."
  }
}
```

### 3.2 插件设计

两个插件同构，照 `Member/Authorization/AuthPlugin.php` 形态（`'biz_content' => $rocket->getParams()`）：

- **做什么**：设置 `method`；把 params 直接作为 `biz_content`；清理公共参数 `app_auth_token`
- **下划线键剥离**：`_action` 等内部参数由 `FormatPayloadBizContentPlugin` 内 `filter_params` 自动过滤（**已验证** `vendor/yansongda/artful/src/functions.php:66`，`_` 前缀键与 null 值均被剥离）
- **边界/防御**：`StartPlugin` 会无条件写入公共参数 `app_auth_token`（取自 config / `_app_auth_token`），而**授权链路本身必须以 ISV 纯净身份发起**，故插件在 `mergePayload` **之后**对 payload 执行 `forget('app_auth_token')`。顺序说明：裸 `Rocket` 的 payload 可空（`Rocket::$payload: ?Collection`，已验证），`mergePayload` 自带 null 防御（先补 `new Collection()`，已验证 `Rocket.php:86-95`），故 forget 置于 mergePayload 之后即天然安全；forget 发生在签名插件（`AddPayloadSignaturePlugin` 对 `sortKeys()->toString()` 签名）之前，不影响签名正确性
- **TokenAppQueryPlugin** 与 TokenAppPlugin 仅 `method` 值不同

### 3.3 Shortcut 设计

`TokenAppShortcut`，`_action` 分发（`Str::camel($params['_action'] ?? 'default').'Plugins'`，照 `CancelShortcut` 惯例）：

| action | method | 插件链 |
|---|---|---|
| `default`（缺省） | `alipay.open.auth.token.app` | StartPlugin → TokenAppPlugin → FormatPayloadBizContentPlugin → AddPayloadSignaturePlugin → AddRadarPlugin → VerifySignaturePlugin → ResponsePlugin → ParserPlugin |
| `query` | `alipay.open.auth.token.app.query` | 同上，业务插件换 TokenAppQueryPlugin |

- 换取与刷新**共用 default**（同一 API，`grant_type` 由用户 params 决定，不设冗余 action）
- 非法 action 抛 `InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID)`
- **对外调用名为 `token_app`（而非 `tokenApp`）**：`Alipay::__call` 先 `strtolower($shortcut)`（`src/Provider/Alipay.php:63`）再 `Str::studly`（`vendor/yansongda/supports/src/Str.php:290-295`，`ucwords` 实现，单词内大写丢失），转换链完整验证：`token_app` → strtolower → `token_app` → studly（`_` 转空格再 ucwords）→ `'TokenApp'` → `Yansongda\Pay\Shortcut\Alipay\TokenAppShortcut` ✓；而 `tokenApp` → `tokenapp` → `'Tokenapp'` → 类不存在 → Linux/CI 大小写敏感文件系统下运行时必抛 `InvalidParamsException`（macOS 默认大小写不敏感 FS 会侥幸命中文件，形成本地绿线上崩的隐蔽缺陷；现有 Shortcut 全为单词条，此坑未暴露过）。**二者不可兼得：若偏好 `tokenApp` 驼峰调用名，则类/文件名须改为 `TokenappShortcut`——待用户醒后二选一确认，默认采用 `token_app`**
- 返回 `$rocket->getDestination()`（`Collection`），传 `_return_rocket` 时返回 `Rocket`

### 3.4 使用示例（写进用户文档）

```php
// ① 回调页接收 app_auth_code（state 原样回传防 CSRF；code 一次性、24h 有效）
// ② 换取（注意调用名为 snake_case 的 token_app，见 §3.3 命名说明）
$result = Pay::alipay()->token_app([
    'grant_type' => 'authorization_code',
    'code' => $code,
]);
$token = $result->get('app_auth_token');          // 入库；app_auth_token 长期有效（见下述口径），
                                                  // 以 app_refresh_token + re_expires_in 管理刷新窗口

// ③ refresh_token 刷新窗口内刷新
Pay::alipay()->token_app(['grant_type' => 'refresh_token', 'refresh_token' => $refreshToken]);

// ④ 查询授权信息（auth_methods 确认商户授权了哪些接口）
Pay::alipay()->token_app(['_action' => 'query', 'app_auth_token' => $token]);

// ⑤ 代商户调用业务接口（V2 公共参数 / V3 请求头 alipay-app-auth-token，行为同现状）
Pay::alipay()->scan(['_app_auth_token' => $token, /* ... */]);
```

> 令牌口径（官方注释）：`app_auth_token` **长期有效**，无需按有效期轮换；需消费的是 `app_refresh_token` 及其 `re_expires_in` 刷新窗口；商户解除授权等场景会主动失效，业务侧需有兜底（捕获业务错误后重新走授权）。

授权链接（仅文档说明，不提供 helper）：

```
https://openauth.alipay.com/oauth2/appToAppAuth.htm?app_id={第三方应用APPID}&redirect_uri={urlencode(回调地址)}&state={自定义串}
```

### 3.5 兼容性设计

- **纯增量**：不改任何现有类/配置/行为；新目录 PSR-4 自动覆盖，composer.json 无需变更；多词 Shortcut 的对外调用名遵循 `__call` 的 `strtolower`+`studly` 转换链（snake_case 输入）
- **响应链零改动**：`ResponsePlugin` 包裹键自动定位（已验证无 method 白名单）；`VerifySignaturePlugin` 对 destination 去除 `_sign` 后用支付宝公钥验签，新接口天然兼容
- **业务错误**：与其他 V2 接口一致——响应无 `sign` 且 `code≠10000` 时抛 `InvalidResponseException(RESPONSE_BUSINESS_CODE_WRONG)`；**带签名的业务错误响应验签通过后按 `Collection` 原样透传**（已验证 `ResponsePlugin.php:37` 条件 `empty($sign) && '10000' !== code`），业务侧自行判断 code
- **授权链接不提供 helper**（用户已确认）：文档说明 URL 拼接规则
- **与现有插件的关系**：`Member/Authorization/*`（用户级）不受影响；`Open/Authorization`（应用级）为独立新增

## 4. 推进策略

- **阶段 1（契约与实现）**：插件+测试 → Shortcut+测试 → `@method` 注解；每步 `composer test` + `composer analyse` + `composer cs-fix` 全绿
- **阶段 2（文档与收尾）**：用户文档+侧边栏 → CHANGELOG
- **可选 spike（0\* 软依赖）**：若可获得真实授权环境，抓取一次真实响应存 `docs/evidence/` 消除有效期字段名歧义；默认跳过（SDK 透传不受影响）
- **回滚**：纯增量变更，直接 revert 对应 commit 即可，无配置/数据迁移

## 5. 风险与对策

| 风险 | 严重度 | 对策 |
|---|---|---|
| 响应有效期字段名歧义（V2 网关历史文档 vs 官方 OpenAPI 模型） | 低 | SDK 不映射响应字段，透传规避；官方口径已明确 `expires_in` 作废、`app_auth_token` 长期有效；文档示例标注「以实际响应为准」；可选 spike 实测 |
| `StartPlugin` 全局 `app_auth_token` 污染授权请求 | 低 | 插件内 `forget('app_auth_token')` 防御；单测断言 payload 无该键 |
| `_action` 泄漏进 biz_content | 低 | `filter_params` 剥离已验证；单测断言 biz_content JSON 无 `_` 键 |
| 与现有 `Member/Authorization` 混淆 | 低 | 独立 `Open/Authorization` 命名空间 + `@see` 官方文档链接；文档中显式区分 |
| 盲区：网关对「换 token 请求携带 app_auth_token 公共参数」的行为未知 | 低 | forget 防御后不存在该场景 |
| 多词 Shortcut 方法名与 `__call` 转换链不匹配（如 `tokenApp`） | 高 | 已定案：对外调用名 `token_app`（snake_case），类名 `TokenAppShortcut`；转换链已逐环验证；如用户偏好驼峰调用名需同步改类名为 `TokenappShortcut`（二选一，醒后确认） |

## 6. 监控与可观测性

本地 SDK 不适用，裁剪。复用现有 PSR-14 事件（`MethodCalled` 等）与 `Logger` 通道，无新增。

## 附录：官方参考

- 换取/刷新应用授权令牌：https://opendocs.alipay.com/open/02qq4l （https://opendocs.alipay.com/isv/04h3uf）
- 查询应用授权信息：https://opendocs.alipay.com/isv/04hgcp
- 第三方应用授权总览：https://opendocs.alipay.com/open/10603/drudqy
- 官方 PHP SDK 模型（字段权威来源）：https://github.com/alipay/alipay-sdk-php-all（`v3/src/Model/AlipayOpenAuthTokenApp{Model,ResponseModel}.php`；其中 `setExpiresIn` 注释明确「该字段已作废，应用令牌长期有效」，`setReExpiresIn` 注释「刷新令牌的有效时间（从接口调用时间作为起始时间），单位到秒」）

## 变更记录

- 2026-09-09（PR #1206 review）：对外调用名由 `token_app` 更名为 `auth`（`AuthShortcut`），分发改为 `_action` 缺省 `token_app`（换取/刷新）/ `query`（查询）；用户文档由 v2 文档站迁移至 v3。§3.3/§3.4 中 `token_app` 调用形态的描述以本记录为准。
