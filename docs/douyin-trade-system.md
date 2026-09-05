# 技术设计：抖音支付重写为新交易系统（Issue #1095）

> **时间**：2026-02-06
> **作者**：GLM-5.3 + yansongda
> **状态**：经过人工审核确认（对话内批准，含三项决策：CHANGELOG 记 v3.8.0-beta.6；退款审核内联 `RefundShortcut`；预下单兜底不保留）

## 1. 背景与问题

### 1.1 现状

master（v3.8.0-beta.5）的抖音 Provider 为老「担保支付」（ecpay）实现：

- 端点：`/api/apps/ecpay/v1/create_order|query_order|create_refund|query_refund`
- 请求签名：MD5（`mch_secret_salt`，`AddPayloadSignaturePlugin`）
- 回调验签：SHA1（`mch_secret_token`，form 参数 `msg/msg_signature/nonce/timestamp/type`）
- 配置：`mini_app_id` / `mch_secret_token` / `mch_secret_salt` / `mch_id` / `thirdparty_id`

抖音官方已将「通用交易系统」（trade system）设为唯一新接入路径：2023-11 起新增小程序必须使用，逾期平台停用担保支付发起新交易（来源：官方公告 post/674535ad、post/6769033b）。

### 1.2 困境

1. SDK 无法接入抖音现行交易系统，新用户不可用
2. PR #1132（Copilot 草稿）核心契约系统性错误（请求签名写成 HMAC-SHA256、回调验签写成 SHA1、端点与 HTTP 方法与官方不符、缺退款审核与退款申请回调），且基于旧版 master 架构（`Functions.php` 已删除、配置体系已重构），不可作为实现基础
3. 新老体系在鉴权（client_token + RSA）、签名（SHA256-RSA2048）、下单流程（前端 JSAPI 为主）上完全不同，无演进路径

### 1.3 目标（约束条件）

- **删除式重写**：老担保支付插件、快捷方式、测试、配置字段全部删除，新交易系统成为唯一实现
- **全链路覆盖**：JSAPI 下单签名、查询（订单/CPS/退款）、退款、退款审核、三类回调验签
- **遵循 master 现架构**：Trait 承载复用逻辑（照搬 `PaypalTrait::getPaypalAccessToken()` token 缓存模式）、`DouyinConfig` 类型化配置、`_url` 前导 `/`、Provider 常量化
- **破坏性变更显式化**：CHANGELOG（v3.8.0-beta.6）+ 升级文档明确说明

## 2. 整体方案

**核心思路：删除 `Plugin\Douyin\V1\Pay`（老 ecpay）全部代码，按官方 trade_basic 契约在 `Plugin\Douyin\V1` 下重建（结构对齐微信 V2 的 Pay/Refund 分组），复用逻辑落 `DouyinTrait`，`DouyinConfig` 字段推倒重定义。**

```
Pay::douyin()->mini($order)      → JSAPI 下单签名（无 HTTP，输出 data + byteAuthorization）
            ::query($order)       → 查订单/CPS/退款（_action 分发）
            ::refund($order)      → 创建退款 / _action=audit 退款审核
            ::callback($request)  → 支付回调（平台公钥 RSA 验签）
            ::refundCallback(...) → 退款结果回调
            ::preRefundCallback(...) → 退款申请回调（业务方需同步应答）
        │
        ├─ Shortcut → 插件管道：StartPlugin → ObtainClientToken → 业务插件 → AddPayloadBody
        │             → AddRadar(access-token 头) → Response(err_no 校验) → Parser
        └─ DouyinTrait：getDouyinUrl / getDouyinClientToken(缓存) / getDouyinTradeSign / verifyDouyinTradeSign
```

### 文件结构

```
src/
├── Config/DouyinConfig.php              ✎ 字段推倒重定义（见 3.1）
├── Traits/DouyinTrait.php               ✎ 重写（见 3.2/3.3/3.6）
├── Plugin/Douyin/V1/Pay/                ✖ 老 8 插件全删（Pay/{AddPayloadSignature,AddRadar,Callback,Response}Plugin
│                                          + Pay/Mini/{Pay,Query,Refund,QueryRefund}Plugin，全部在 Pay/ 子目录下）
│   ├── AddRadarPlugin.php               ▲ access-token 头 + JSON body（_body 优先，无 GET 分支）
│   ├── ResponsePlugin.php               ▲ err_no 校验（兼容 err_msg/err_tips）
│   ├── ObtainClientTokenPlugin.php      ▲ token 注入（params['_access_token'] 优先）
│   ├── GetClientTokenPlugin.php         ▲ + GetClientTokenResponsePlugin（oauth/client_token 子调用）
│   ├── Pay/                             ▲ SignPlugin(JSAPI) / QueryPlugin / QueryCpsPlugin / CallbackPlugin
│   └── Refund/                          ▲ RefundPlugin / QueryRefundPlugin / AuditPlugin
│                                           / CallbackPlugin / PreRefundCallbackPlugin
├── Shortcut/Douyin/                     ✖ Mini/Query/RefundShortcut 删除
│   ├── MiniShortcut.php                 ▲ JSAPI 签名
│   ├── QueryShortcut.php                ▲ _action: order(默认)/cps/refund
│   └── RefundShortcut.php               ▲ default: 创建退款；_action=audit: 退款审核
├── Provider/Douyin.php                  ✎ URL 常量改 open.douyin.com、方法重定义（见 3.7）
└── Exception/Exception.php              ✧ 复用现有 9222/9405 及通用 SIGN_* 码，原则上不新增
tests/                                   ✖ 老 douyin 测试全删，▲ 全量新测试
web/docs/v3/douyin/*                     ✖ 重写（close/cancel 保留"官方无此 API"）+ quick-start + 升级说明
```

## 3. 详细设计

### 3.1 配置设计（`DouyinConfig` 推倒重定义）

```jsonc
"douyin": {
  "default": {
    "app_id": "ttxxxxxx",                 // 必填，client_key（即小程序 appid）
    "app_secret": "xxx",                  // 必填，获取 client_token
    "app_private_key": "-----BEGIN ...",  // 应用私钥，下单加签用（SignPlugin 运行时校验）
    "platform_public_key": "-----BEGIN ...", // 平台公钥，回调验签用（回调插件运行时校验）
    "notify_url": "https://xx/notify",          // 选填，支付/退款回调默认地址
    "refund_notify_url": "https://xx/refund",   // 选填，退款回调默认地址
    "mode": 0
  }
}
```

| 变更 | 内容 |
|---|---|
| 删除字段 | `mini_app_id`、`mch_secret_token`、`mch_secret_salt`、`mch_id`、`thirdparty_id`（trade_basic 各接口 schema 均无 `thirdparty_id`，已验证；多门店收款用下单参数 `merchant_uid` 透传） |
| 新增字段 | `appId`、`appSecret`、`appPrivateKey`、`platformPublicKey`、`refundNotifyUrl` |
| 新增私有缓存属性 | `accessToken` / `accessTokenExpiry`（Paypal 模式，见 3.2） |
| 必填校验 | `validateRequired()` 仅强制 `app_id` + `app_secret`；两把 RSA 密钥在使用点校验（纯查询用户无需配私钥） |
| 异常码 | 复用 `CONFIG_DOUYIN_INVALID`(9405)、`PARAMS_DOUYIN_URL_MISSING`(9222) 及通用 `SIGN_EMPTY`/`SIGN_ERROR`，不新增常量 |

### 3.2 client_token 获取与缓存（契约已验证：官方文档页面正文 + schema + curl 示例三处一致）

- 端点：`POST https://open.douyin.com/oauth/client_token/`（沙盒 `open-sandbox.douyin.com`）
- 请求体：`{"grant_type": "client_credential", "client_key": "<app_id>", "client_secret": "<app_secret>"}`
- 响应体：`{"data": {"access_token": "clt.xxx", "expires_in": 7200, "error_code": 0, "description": ""}, "message": "success"}`
- 业务接口统一携带请求头 `access-token: <token>`
- 官方频控：5 分钟内 500 次；重复获取使上一 token 失效（5 分钟缓冲）
- **缓存照搬 `PaypalTrait::getPaypalAccessToken()` 模式**（已验证读过源码，src/Traits/PaypalTrait.php:56-91）：Config 对象上缓存，`time() + expires_in - 60` 失效；内部子调用管线 `[StartPlugin, GetClientTokenPlugin, AddRadarPlugin, GetClientTokenResponsePlugin, ParserPlugin]`（顺序对齐 Paypal 同款）
- **子调用防污染**：StartPlugin 会把完整外层 params merge 进 payload（vendor 源码已验证），故 token 子调用仅传最小参数集（仅保留 `_config`）——从结构上同时避免业务字段混入 client_token 请求体、排除 `_return_rocket` 对返回值形态的干扰（PayPal 曾因后者修复 #1196）
- **AddRadar body 语义**：`_body` 优先（业务管线中 AddPayloadBodyPlugin 的产物），为空时回退 `json_encode(filter_params(payload))`——回退分支正是 token 子调用（管线无 AddPayloadBodyPlugin）产生 `{grant_type, client_key, client_secret}` 干净请求体的机制
- 注意：`client_secret` 会随 payload 进入 artful 的请求日志（Logger::info 打印 rocket 全量），文档需提示生产环境关闭 debug 级日志或做日志脱敏
- 已知限制：PHP-FPM 下 Config 为进程内缓存，高并发会重复获取；支持 `params['_access_token']` 外部注入供用户自建缓存，文档说明

### 3.3 下单签名（JSAPI 主路径，契约已验证：官方 request-order-data-sign 页）

服务端职责不是发 HTTP 请求，而是产出前端 `tt.requestOrder(data, byteAuthorization)` 所需的两个值：

```
待签串（五行，每行末尾 \n）:
  POST\n /requestOrder\n {timestamp秒}\n {nonce}\n {data JSON字符串}\n
byteAuthorization = Base64( SHA256withRSA(待签串, app_private_key) )
Header 值: SHA256-RSA2048 appid={app_id},nonce_str={nonce},timestamp={ts},key_version=1,signature={sig}
```

- `data` 为官方 camelCase 字段格式（`outOrderNo`/`totalAmount`/`skuList`/`orderEntrySchema` 等），SDK 透传组装 + JSON 序列化
- 插件形态：`SignPlugin` 产 `data` + `byteAuthorization`；**执行模式对齐 `src/Plugin/Wechat/Virtual/PayPlugin.php`（`setDirection(NoHttpRequestDirection::class)` 在 `$next` 前、`setDestination` 在 `$next` 后；**destination 表达式以 C3/Task 5(a) 的 `{data, byteAuthorization}` 为准，勿照抄微信的 `getPayload()` 表达式**）**，且 MiniShortcut 链为 `[StartPlugin, SignPlugin]` **不挂 ParserPlugin**（ParserPlugin 对非 ResponseInterface 的非空 destination 抛 9208，已验证 vendor 源码）；destination 返回给业务方
- ⚠️ 推断（未实测）：`appid=` 是否带引号（官方 request-order-data-sign 页无引号、签名算法总览页带引号）——**实现以签名算法总览页为准（带引号）**，列入 Task 0 spike 与联调确认项
- ⚠️ 推断（未实测）：`timestamp` 超过 1 小时的请求会被平台拒绝（官方说明），SDK 默认取当前时间

### 3.4 trade_basic 业务接口（契约已验证：各页 openApiMeta schema）

统一模式：`POST https://open.douyin.com/api/trade_basic/v1/developer/<name>/`（**注意尾斜杠**）+ `access-token` 头鉴权，无请求签名，响应顶层 `err_no`/`err_msg`/`log_id`（`data` 为 JSON 对象）：

| 插件 | 端点 | 关键入参 |
|---|---|---|
| `Pay/QueryPlugin` | `order_query/` | `order_id` / `out_order_no` 二选一 |
| `Pay/QueryCpsPlugin` | `query_cps/` | `order_id` / `out_order_no` 二选一 |
| `Refund/RefundPlugin` | `refund_create/` | `order_id` + `out_refund_no` + `refund_reason[]` 必填；`notify_url` 可选（默认取解决方案配置） |
| `Refund/QueryRefundPlugin` | `refund_query/` | `refund_id` / `out_refund_no` / `order_id` 三选一（按订单查上限 50 条） |
| `Refund/AuditPlugin` | `refund_audit_callback/` | `refund_id` + `refund_audit_status`(1 同意/2 拒绝) 必填，拒绝时 `deny_message` 必填 |

**预下单兜底接口（create_order）按用户决策不实现**（官方仅允许低版本用户使用，违规会被惩处）。

### 3.5 回调：验签、解析、应答（契约已验证：common-param + notify-payment-result + refund-notify + refund-callback 页）

三类回调统一 JSON body：`{"version": "3.0", "type": "payment|refund|pre_create_refund", "msg": "<JSON字符串>"}`，验签信息在**请求头**：

```
验签串（三行，每行末尾 \n）: {Byte-Timestamp}\n{Byte-Nonce-Str}\n{原始body}\n
校验: SHA256withRSA(验签串, platform_public_key) === Base64Decode(Byte-Signature)
```

- **必须用原始 body 字符串验签**（官方明确：经框架解析/排序后必失败）→ 回调插件统一从 `params['_request']`（ServerRequestInterface）取原始 body 与回调头；`callback()` 因 `ProviderInterface`（src/Contract/ProviderInterface.php:45）约束**保持宽签名** `callback(array|ServerRequestInterface|null $contents = null, ?array $params = null)`，内部把入参归一为 ServerRequestInterface（null → `ServerRequest::fromGlobals()`；array 无回调头信息 → 抛 `PARAMS_CALLBACK_REQUEST_INVALID`），`refundCallback()`/`preRefundCallback()` 不受接口约束、用窄签名 `(ServerRequestInterface $request, ?array $params = null)`
- 老担保支付回调（form 参数 + SHA1+token）随老代码删除，不保留兼容
- 应答：HTTP 200 + `{"err_no":0,"err_tips":"success"}`（`success()` 现有实现恰好兼容，保留）；不响应平台重试（支付回调 12s/12s/33s/1m/2m/3m/4m/5m/6m/7m）
- 三个回调入口各自校验 body 顶层 `type` 字段与入口语义匹配（`payment`/`refund`/`pre_create_refund`），不匹配抛 `PARAMS_CALLBACK_REQUEST_INVALID`——防误接线导致静默错配、平台无限重试卡单
- `PreRefundCallbackPlugin`（退款申请回调 `type=pre_create_refund`）：验签解析后返回 `Collection`（含 `need_refund_audit`/`refund_audit_deadline`/`refund_id` 等）；**同步应答 `data.out_refund_no` + `order_entry_schema` 由业务方构造**（SDK 职责边界，文档给完整示例；不响应平台会一直重试并卡单）
- ⚠️ 推断（未实测）：`pre_create_refund` 页示例 curl 出现 `Byte-Authorization` 头（疑文档残留），与 common-param 的 `Byte-Signature` 冲突——实现以 `Byte-Signature` 三行验签为主

### 3.6 响应校验

Trade `ResponsePlugin`：HTTP 2xx + `err_no === 0`，异常消息取 `err_msg ?? err_tips`（两套格式并存，已验证：trade_basic 用 `err_msg`，回调应答/预下单用 `err_tips`）。

### 3.7 Provider 方法重定义

| 方法 | 说明 |
|---|---|
| `mini($order)` | JSAPI 下单签名（`__call` → `MiniShortcut`） |
| `query($order)` | `_action`: `default/order`（order_query）、`cps`（query_cps）、`refund`（refund_query） |
| `refund($order)` | 创建退款；`_action=audit` 退款审核（用户已确认内联） |
| `callback($contents = null, $params = null)` | 支付回调（宽签名满足 ProviderInterface，内部强制 ServerRequestInterface） |
| `refundCallback(ServerRequestInterface $request)` / `preRefundCallback(...)` | 退款结果 / 退款申请回调（新方法，窄签名） |
| `success()` | 不变，`{"err_no":0,"err_tips":"success"}` |
| `cancel()` / `close()` | 保留抛不支持（新系统同样无此 API） |

- URL 常量：`URL = [normal → https://open.douyin.com, sandbox → https://open-sandbox.douyin.com, service → https://open.douyin.com]`（预下单不实现后，所有端点单域名）
- **不实现 `mergeCommonPlugins()`**（该方法已被 #1173 从全部 Provider 移除，重新引入属设计性偏差）；公共插件链完全由 Shortcut 组装：`StartPlugin → ObtainClientTokenPlugin → [业务] → AddPayloadBodyPlugin → AddRadarPlugin → ResponsePlugin → ParserPlugin`

## 4. 推进策略

```
Wave 0  前置：自 master 切出特性分支 feature/douyin-trade-system 并全程在该分支提交（硬性前提）
        契约 spike（0* 软依赖：沙盒 client_token 实测、Byte-Authorization 引号格式联调确认）
Wave 1  清场 + 基础设施：删除老实现 + 生成 RSA fixture + DouyinConfig 重定义 + DouyinTrait 签名/验签（串行）
Wave 2  基础插件：token 三件套 + AddRadar + Response（串行：后一任务依赖前一任务产出的类）
Wave 3  业务插件：Pay 组 │ 回调组 │ Refund 组（可并行）
Wave 4  Shortcut + Provider 接线 + 全量测试
Wave 5  文档重写 + CHANGELOG v3.8.0-beta.6 + 升级说明
```

**回滚要点**：单特性分支整体 revert，无兼容负担——出问题 revert commit 即可；无灰度/配置回滚概念。

## 5. 风险与对策

| 风险 | 严重度 | 对策 |
|---|---|---|
| 破坏性变更：仍在用老担保支付的用户升级即坏（含存量担保支付订单退款/查询走老接口的场景） | 高 | CHANGELOG 置顶 + 升级文档给「3.7 → 3.8 抖音迁移指引」；发布说明明确标注 breaking |
| 契约来自官方文档静态分析，未经真实商户实测 | 高 | spike 先行（0* 软依赖）+ 联调清单写入 plan + 文档标注未实测项 |
| 回调验签头文档冲突（`pre_create_refund` 示例出现 `Byte-Authorization`） | 中 | 以 `Byte-Signature` 三行串为主实现；联调时验证；测试覆盖三类回调 |
| `Byte-Authorization` 的 `appid=` 引号格式两处官方文档不一致 | 中 | 按签名算法总览页（带引号）实现，spike/联调确认；如需调整属机械性修正（单常量字符串） |
| token 进程内缓存 FPM 高并发重复获取触发频控（500 次/5min） | 中 | `_access_token` 外部注入 + 文档提示自建缓存；频控报错信息明确 |
| 官方「重复获取使上一 token 失效（5 分钟缓冲）」语义未实测，若实际即时失效，高并发下重复获取会互相打断 | 中 | `_access_token` 外部注入兑底；Task 0 spike 顺带验证失效语义；文档提示自建共享缓存 |
| RSA 密钥格式错误（PKCS1/PKCS8）报错晦涩 | 中 | 加载/签名失败抛明确中文异常；文档附 openssl 生成/查看密钥命令 |

## 6. 监控与可观测性

SDK 库无线上监控诉求，**裁剪本节**；沿用既有 `Logger::debug/info` 插件日志惯例 + 全量插件测试覆盖替代。

## 附录 A：PR #1132 契约比对（决策依据）

| 契约点 | PR #1132 | 官方文档（已验证） | 判定 |
|---|---|---|---|
| 请求签名 | HMAC-SHA256(k=v 排序拼接, app_secret) | 下单类 SHA256-RSA2048 应用私钥；trade_basic 无请求签名靠 access-token 头 | ❌ 错 |
| 回调验签 | SHA1(sort[app_secret,timestamp,nonce,msg]) | 平台公钥 SHA256withRSA 三行串 + `Byte-Signature` 头 | ❌ 错 |
| 下单 | POST open.douyin.com/api/trade/v1/create_order | JSAPI 主路径 tt.requestOrder；预下单兜底在 developer.toutiao.com | ❌ 错 |
| 查询/退款 | GET api/trade/v1/* | POST /api/trade_basic/v1/developer/* | ❌ 错 |
| client_token | POST oauth/client_token/ + data.error_code 校验 | 一致 | ✅ 唯一正确 |
| 覆盖度 | 缺退款审核、退款申请回调、回调同步应答 | 官方均有明确文档 | ❌ 缺 |

**结论：PR #1132 不作为实现基础，仅命名空间划分与插件清单骨架有参考价值。处置：由维护者在 GitHub 上关闭或评论说明由本次重写取代（维护者操作项，不阻塞实施）。**

## 附录 B：契约快照来源

- 通用参数：https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/common-param
- 生成下单参数与签名：.../general/order/request-order-data-sign
- 签名算法总览：https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/signature-algorithm
- 查询订单 / 查询 CPS / 支付结果回调：.../general/order/query-order 、query-cps 、notify-payment-result
- 发起退款 / 查询退款 / 退款审核：.../general/refund/create-refund 、query-refund 、refund-audit
- 退款申请回调 / 退款结果通知：.../general/refund/refund-callback 、refund-notify
- 获取 client_token：.../basic-abilities/interface-request-credential/non-user-authorization/get-client_token
- 下线公告：https://developer.open-douyin.com/forum/bulletin/post/674535ad67ccd35b42b71861 、https://developer.open-douyin.com/forum/synthesize/post/6769033bff7dbb42ad955679

## 附录 C：遗留维护者操作项

1. 关闭 PR #1132（或留评论说明由本次重写取代）
2. 提供/申请沙盒 client_key + client_secret 供 Task 0 spike（可选，不阻塞）
