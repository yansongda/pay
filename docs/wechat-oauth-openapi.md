# 微信 Openapi 插件域与 OAuth 用户身份能力 技术设计文档

> **时间**：2026-09-09
> **作者**：DeepSeek V4 Pro + yansongda
> **状态**：经过人工审核确认

## 1. 背景与问题

**现状**：v3.8（当前最新 tag v3.8.0-beta.5）中支付宝侧已具备用户身份能力（`Plugin/Alipay/V2/Member/Authorization/*`、`DetailPlugin`），微信侧仅有支付能力。微信虚拟支付模块已打通 `api.weixin.qq.com` 通路（`getWechatUrl()` 对 `/xpay/`、`/cgi-bin/` 前缀返回 `Wechat::URL_VIRTUAL`）。

**困境**：

1. 用户需获取微信用户身份（公众号网页授权、小程序 code2session）时必须另装 easywechat——即 issue #788 诉求，两年未闭环；
2. `Wechat::URL_VIRTUAL` 命名已名不副实（OAuth `/sns/*` 同走 `api.weixin.qq.com`），且现有路由**不认 `/sns/` 前缀**（已验证，读源码 `WechatTrait::getWechatUrl()`）；
3. 现有管道对 GET 无 query 支持（`AddPayloadBodyPlugin` 无条件打包 body，已验证），OAuth 要求参数在 URL query。

**目标**：

- **独立的 Openapi 插件域**：承接 `api.weixin.qq.com` 上与具体支付业务解耦的通用能力；
- **纯支付用户零影响**：新增配置字段全部可选、不改既有管道行为；
- **命名消除 token 歧义**：按接口场景统一命名；
- **BC 变更明示且可控**：按已批准决策执行，UPGRADE 文档记录。

## 2. 整体方案

**核心思路**：新增 `Plugin/Wechat/Openapi/` 插件域；OAuth 走「**业务插件自拼 URL query + 空 body**」的 GET 管道，统一响应校验合并为一个 `ResponsePlugin`。

```
Pay::wechat()->oauth(['_action' => 'session', 'js_code' => '...'])
        │
        ▼
  OauthShortcut（_action → 业务插件）
┌─────────────────────────────────────────────────────────────┐
│ StartPlugin → Oauth业务插件 → Wechat AddRadarPlugin →        │
│ Openapi\ResponsePlugin → ParserPlugin                        │
└─────────────────────────────────────────────────────────────┘
        │                                    │
        ▼                                    ▼
 _url 自拼 /sns/**?query（含secret）   getWechatUrl(): /sns/* → api.weixin.qq.com
 _body = ''（GET 无请求体）
```

**文件结构**（`+`新增 `~`修改 `»`迁移引用）：

```
src/
├── Provider/Wechat.php                    ~ URL_VIRTUAL → URL_OPENAPI
├── Traits/WechatTrait.php                 ~ /sns/ 分支 + 引用 GetStableTokenPlugin
├── Config/WechatConfig.php                + mpAppSecret / miniAppSecret（可选）
├── Plugin/Wechat/Openapi/                 + 新插件域
│   ├── GetStableTokenPlugin.php           » Virtual/GetAccessTokenPlugin 更名迁入（/cgi-bin/stable_token）
│   ├── ResponsePlugin.php                 » Virtual/CheckResponsePlugin 迁入 + 合并 HTTP 状态检查
│   └── Oauth/
│       ├── WebAccessTokenPlugin.php       + /sns/oauth2/access_token（网页授权，用户级）
│       ├── UserInfoPlugin.php             + /sns/userinfo
│       ├── RefreshTokenPlugin.php         + /sns/oauth2/refresh_token（用户级）
│       └── Code2SessionPlugin.php         + /sns/jscode2session（会话级）
├── Shortcut/Wechat/OauthShortcut.php      + 4 个 _action
├── Shortcut/Wechat/VirtualShortcut.php    ~ 响应插件位合并
tests/                                      ~ 改 4 + 增 7 / 删 2（迁自 Virtual 域）
web/docs/v3/upgrade/v3.8.md                ~ BREAKING CHANGES 3 条 bullet
web/docs/v3/wechat/oauth.md                + 使用文档（独立页面，对齐 virtual.md 先例）
```

## 3. 详细设计

### 3.1 配置设计（数据格式已验证：读 `WechatConfig` 源码）

```json
{
  "wechat": {
    "default": {
      "mp_app_id": "公众号appid",
      "mp_app_secret": "公众号appsecret",
      "mini_app_id": "小程序appid",
      "mini_app_secret": "小程序appsecret"
    }
  }
}
```

| 字段 | 类型 | 必填 | 说明 |
|---|---|---|---|
| `mp_app_secret` | ?string | 否 | 仅 `web_token`/`refresh` 需要 |
| `mini_app_secret` | ?string | 否 | 仅 `session` 需要 |

- 不加入 `validateRequired()`；secret 优先级 `params 显式传入 → config`（仿虚拟支付惯例）。

### 3.2 GET 管道与业务插件（关键契约已验证）

- `getWechatBody()` 要求 `_body` 必存（缺失抛 `PARAMS_WECHAT_BODY_MISSING`），故**业务插件自设 `_body => ''`**，且不装 `AddPayloadBodyPlugin`（否则会覆盖 `_body`）——GET 请求体为空；
- `_url` 以 `/sns/` 开头即可被新路由分支补全域名，业务插件拼 query 对齐 `Virtual/AddPayloadSignaturePlugin::appendQueryParams` 先例。

```
伪代码（Code2SessionPlugin）：
mergePayload([
  '_method' => 'GET',
  '_url' => '/sns/jscode2session?'.http_build_query([
      'appid'      => params.appid  ?? config.mini_app_id     （缺失→InvalidConfigException）
      'secret'     => params.secret ?? config.mini_app_secret（缺失→InvalidConfigException）
      'js_code'    => params.js_code（缺失→InvalidParamsException）
      'grant_type' => 'authorization_code',
  ]),
  '_body' => '',
])
```

### 3.3 接口规格（已验证：微信官方文档 + 存档快照；错误响应经真实 HTTP 实测）

| 插件 | 请求 | query 参数 |
|---|---|---|
| `WebAccessTokenPlugin` | GET `/sns/oauth2/access_token` | `appid`、`secret`、`code`、`grant_type=authorization_code` |
| `UserInfoPlugin` | GET `/sns/userinfo` | `access_token`、`openid`、`lang`（可选默认 `zh_CN`） |
| `RefreshTokenPlugin` | GET `/sns/oauth2/refresh_token` | `appid`、`grant_type=refresh_token`、`refresh_token` |
| `Code2SessionPlugin` | GET `/sns/jscode2session` | `appid`、`secret`、`js_code`、`grant_type=authorization_code` |
| `GetStableTokenPlugin` | POST `/cgi-bin/stable_token`（body JSON） | `grant_type=client_credential`、`appid`、`secret`、`force_refresh` 可选 |

```bash
curl 'https://api.weixin.qq.com/sns/jscode2session?appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code'
```

```json
{ "openid": "xxx", "session_key": "xxx", "unionid": "xxx", "errcode": 0, "errmsg": "xxx" }
```

**关键契约**：

- 参数全小写（`appid`/`js_code`/`grant_type`）；`grant_type=client_credential` 为**单数**（已验证，官方原文）；
- 成功/失败**不依赖 HTTP 状态码**（实测错误也返回 HTTP 200），错误统一 `{"errcode": int, "errmsg": "..."}`，`errmsg` 常带 `rid:<uuid>` 后缀——**代码不得按 errmsg 全文匹配**；
- `code2session` 成功响应自带 `errcode:0`，与 `null !== errcode && 0 !== errcode` 判断兼容（已验证）；
- 公众号/小程序 appid+secret 是**独立凭据对**，不互通；`unionid` 需开放平台绑定，不硬编码假设。

### 3.4 Openapi\ResponsePlugin（合并两层，行为等价性已验证）

```
伪代码：
if (!should_do_http_request(direction) || destinationOrigin is null) return;   ← 原 CheckResponse 短路
非 2xx → 抛 RESPONSE_CODE_WRONG，文案“微信开放接口返回状态码异常”            ← 原 Wechat\ResponsePlugin 职责
errcode !== null && !== 0 → 抛 RESPONSE_BUSINESS_CODE_WRONG，文案“微信开放接口返回业务异常”  ← 原 CheckResponse 职责
```

**行为顺序论证**（已验证，读 `Artful::artful()`/`Artful::ignite()`、supports `Pipeline`、artful `ParserPlugin` 与双侧插件源码）：

1. HTTP 请求由 `Artful::ignite()` 在管道最内层发出（`Artful.php:264` 的 `->then(fn ($rocket) => self::ignite($rocket))`，`ignite` 内 `$http->sendRequest(...)` 并 `setDestination(clone $response)->setDestinationOrigin(...)`——**任何状态码（含 500）都会设置 destination**）；`ParserPlugin` 只负责 `$direction->guide()` 解包响应体，不发请求。
2. supports `Pipeline` 为 `array_reduce(array_reverse($pipes), ...)` 洋葱模型（`Pipeline.php:47-49`）：原管道数组 `[..., AddRadar, CheckResponse, Response, Parser]` 的弹栈顺序为 ignite（发 HTTP）→ Parser 后置 → `Wechat\ResponsePlugin` 后置（HTTP 检查，先抛）→ `CheckResponsePlugin` 后置（errcode 检查，后抛）。故 **HTTP 状态检查先于 errcode 检查**。
3. 合并插件按「HTTP 先、errcode 后」顺序，与原管道一致；HTTP 检查抛异常时 errcode 检查不会执行（而非「HTTP 错误时无 destination」）。
4. 已知理论边界（不可达，仅记录）：direction 为 `NoHttpRequestDirection` 且 destinationOrigin 为非 2xx `Response` 时，原 `Wechat\ResponsePlugin`（无 `should_do_http_request` 守卫）会抛 `RESPONSE_CODE_WRONG`，合并版（守卫短路）不抛；SDK 内无产生该状态的代码路径。

连锁影响：`VirtualShortcut::serverSidePlugins()` 的 `CheckResponsePlugin + ResponsePlugin` 两个位置合为 `Openapi\ResponsePlugin` 一个（管道插件数 8→7）；存量测试 `testHttpStatusCodeErrorStillChecksErrcode` 的断言由 `RESPONSE_BUSINESS_CODE_WRONG` 改为 `RESPONSE_CODE_WRONG`（单插件直测视角下 HTTP 检查先执行），并新增**管道级用例**（mock HTTP 500 + errcode body → `RESPONSE_CODE_WRONG`）验证真实弹栈中 HTTP 检查先抛；errcode 业务异常路径由单插件用例（HTTP 200 + errcode 43001 → `RESPONSE_BUSINESS_CODE_WRONG`）覆盖。`getWechatVirtualAccessToken()` 内部管道仅换类引用（`GetStableTokenPlugin`），其 `Wechat\ResponsePlugin` 不动。

异常文案变化（`'微信虚拟支付返回业务异常'`/`'微信返回状态码异常'` → `'微信开放接口返回业务异常'`/`'微信开放接口返回状态码异常'`）作为 BC 申报进 UPGRADE bullet；异常码（`RESPONSE_CODE_WRONG`/`RESPONSE_BUSINESS_CODE_WRONG`）不变。

### 3.5 兼容性设计（BC 清单）

| 变更 | 类型 | 影响面（已验证） |
|---|---|---|
| `Wechat::URL_VIRTUAL` 删除 → `URL_OPENAPI` | change | 定义 1 处 + 引用 1 处 |
| `Virtual\GetAccessTokenPlugin` → `Openapi\GetStableTokenPlugin` | change | WechatTrait 2 处 + 1 测试 |
| `Virtual\CheckResponsePlugin` → `Openapi\ResponsePlugin`（合并） | change | VirtualShortcut 1 处 + `tests/Provider/WechatTest.php` 10 处（4 用例）+ `tests/Stubs/Plugin/VirtualCheckResponsePluginStub.php`（extends）+ 1 测试 |
| 其余 Virtual 业务插件（AddPayloadSignaturePlugin、Order/Currency/Goods/Subscribe/Withdraw） | 不动 | 语义与虚拟支付业务绑定 |
| `docs/fix-1186.md` 与 `docs/learning/fix-1186.md` 旧类名字符串 | 不动 | 历史设计记录 |

`getWechatUrl()` 新路由：

```
http 开头 → 原样 | requestVirtualPayment → 原样
/xpay/ | /cgi-bin/ | /sns/ → URL_OPENAPI + url        ← 新增 /sns/
其余 → URL[mode] + url
```

## 4. 推进策略

**单 PR 三阶段，逐阶段可验证、整体可 revert**：

1. **迁移与路由**：URL_OPENAPI + `/sns/` 分支 + `GetStableTokenPlugin`/`ResponsePlugin` 迁入更名 + UPGRADE bullet → 验证：存量测试全绿；
2. **新增能力**：WechatConfig 两字段 + Oauth 4 插件 + OauthShortcut + 单测 → 验证：`composer test` 绿；
3. **文档**：oauth.md + sidebar 注册 → 验证：docs build。

**回滚**：`git revert` 整 PR。

## 5. 风险与对策

| 风险 | 严重度 | 对策 |
|---|---|---|
| secret 进 URL query + `AddRadarPlugin` 日志打印 rocket（`_url` 含 secret） | 中 | 仓库无脱敏先例（已验证），沿用 virtual.md 惯例：oauth.md 醒目风险提示；`_body=''` 已消除 body 侧暴露 |
| BC 变更影响自维护插件的用户 | 中 | UPGRADE/v3.8.md 三条 `change:` bullet + issue #788 回复 |
| 快照用户 `is_snapshotuser=1` 时 openid 为虚拟账号 | 低 | oauth.md 明确提示 |
| `code` 一次性且 5 分钟过期（官方原文） | 低 | 文档注明，业务侧责任 |
| `errmsg` 带 `rid:` 后缀 | 低 | 代码不匹配全文，仅透传 |
| `userinfo` 需 `snsapi_userinfo` 授权否则 48001 | 低 | 文档标注授权要求 |
| `session_key` 敏感性 | 低 | 文档警示 |

## 6. 监控与可观测性

SDK 库无独立监控通道，沿用 Artful Logger 现有 debug/info 通道；secret 落盘风险由第 5 节文档提示覆盖。

## 附录：配置与调用示例

```php
// 仅小程序登录（session）：需 mini_app_id + mini_app_secret
Pay::config(['wechat' => ['default' => [
    'mch_id' => ..., 'mch_secret_key' => ..., 'mch_secret_cert' => ..., 'mch_public_cert_path' => ...,
    'mini_app_id' => 'wx...', 'mini_app_secret' => '...',
]]]);

// 网页授权（web_token/refresh）：需另配 mp_app_id + mp_app_secret；按 3.1 字段表，缺对应凭据会抛 InvalidConfigException

Pay::wechat()->oauth(['_action' => 'session',   'js_code' => '...']);
Pay::wechat()->oauth(['_action' => 'web_token', 'code' => '...']);
Pay::wechat()->oauth(['_action' => 'userinfo',  'access_token' => '...', 'openid' => '...']);
Pay::wechat()->oauth(['_action' => 'refresh',   'refresh_token' => '...']);
```