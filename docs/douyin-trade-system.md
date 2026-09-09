# 技术设计：抖音支付通用交易系统（trade_basic）

> **状态**：已实施（PR #1203）。实施过程中经两轮维护者 review 调整（2026-09-07 配置键、插件结构与回调入口；2026-09-08 文档链接与插件重命名），本文档为与代码一致的最终实现说明。

## 1. 背景与目标

抖音官方将「通用交易系统」（trade_basic）设为唯一新接入路径，老「担保支付」（ecpay）已停止发起新交易。本方案对抖音 Provider 做**删除式重写**：老担保支付插件、快捷方式、测试、配置字段全部删除，新交易系统成为唯一实现。破坏性变更记录于 CHANGELOG（v3.8.0-beta.6）。

**预下单兜底接口（create_order）不实现**：官方仅允许低版本客户端用户使用，违规使用会被平台惩处；JSAPI（`tt.requestOrder`）为唯一下单路径。

## 2. 整体架构

```
Pay::douyin()->mini($order)      → JSAPI 下单签名（无 HTTP，返回 data + byteAuthorization）
            ::query($order)       → 查订单/CPS/退款（_action 分发）
            ::refund($order)      → 创建退款 / _action=audit 退款审核
            ::callback($request)  → 统一回调入口（payment 支付结果 / refund 退款结果 / pre_create_refund 退款申请）
            ::success()           → {"err_no":0,"err_tips":"success"}
        │
        ├─ Shortcut → 插件管道：StartPlugin → ObtainClientToken → 业务插件 → AddPayloadBody
        │             → AddRadar(access-token 头) → Response(err_no 校验) → Parser
        └─ DouyinTrait：getDouyinUrl / getDouyinClientToken(缓存) / getDouyinTradeSign / verifyDouyinTradeSign
```

例外：`MiniShortcut` 链为 `[StartPlugin, InvokePlugin]`，不发 HTTP 请求、不挂 Parser。

### 文件结构

```
src/
├── Config/DouyinConfig.php
├── Traits/DouyinTrait.php               getDouyinUrl / getDouyinClientToken / getDouyinTradeSign / verifyDouyinTradeSign
├── Plugin/Douyin/V1/
│   ├── AddRadarPlugin.php               access-token 请求头 + JSON body（_body 优先）
│   ├── CallbackPlugin.php               统一回调：验签 + type 非空校验 + msg 解析
│   ├── GetClientTokenPlugin.php         oauth/client_token 子调用 + data.error_code 校验
│   ├── ObtainClientTokenPlugin.php      token 注入（params['_access_token'] 优先）
│   ├── ResponsePlugin.php               err_no 校验
│   ├── Pay/
│   │   ├── InvokePlugin.php             JSAPI 下单签名（NoHttpRequestDirection，不发 HTTP）
│   │   ├── QueryPlugin.php              order_query
│   │   └── QueryCpsPlugin.php           query_cps
│   └── Refund/
│       ├── RefundPlugin.php             refund_create（未传 notify_url 时注入配置 notify_url）
│       ├── QueryPlugin.php              refund_query
│       └── AuditPlugin.php              refund_audit_callback
├── Shortcut/Douyin/
│   ├── MiniShortcut.php                 [StartPlugin, InvokePlugin]
│   ├── QueryShortcut.php                _action: default/order（order_query）/ cps（query_cps）/ refund（refund_query）
│   └── RefundShortcut.php               default: 创建退款；_action=audit: 退款审核
└── Provider/Douyin.php                  URL 常量 + mini/query/refund/callback/success；cancel/close 抛不支持
```

## 3. 实现说明

### 3.1 配置（`DouyinConfig`）

```jsonc
"douyin": {
  "default": {
    "app_id": "ttxxxxxx",                     // 必填，client_key（即小程序 appid）
    "app_secret": "xxx",                      // 必填，获取 client_token
    "app_private_key": "-----BEGIN ...",      // 应用私钥，下单加签用（InvokePlugin 运行时校验）
    "douyin_public_key": "-----BEGIN ...",    // 平台公钥，回调验签用（回调插件运行时校验）
    "notify_url": "https://xx/notify",        // 选填，支付/退款回调默认地址
    "mode": 0
  }
}
```

- 必填校验仅 `app_id` + `app_secret`（`CONFIG_DOUYIN_INVALID` 9405）；两把 RSA 密钥在使用点校验，纯查询用户无需配置私钥
- `_url` 缺失时抛 `PARAMS_DOUYIN_URL_MISSING`（9222）

### 3.2 client_token 获取与缓存

- 端点：`POST /oauth/client_token/`（域名由 `mode` 决定），请求体 `grant_type=client_credential` + `client_key` + `client_secret`
- 子调用管线：`[StartPlugin, GetClientTokenPlugin, AddRadarPlugin, ParserPlugin]`；`data.error_code` 非零由 `GetClientTokenPlugin::validateResponse()` 校验
- **子调用仅传最小参数集（仅 `_config`）**：StartPlugin 会把完整外层 params merge 进 payload，传完整 params 会把业务字段混入 client_token 请求体，且 `_return_rocket` 会改变返回值形态（PayPal 曾因此修复 #1196）
- 缓存：`DouyinTrait::$clientTokens` 静态数组按 `app_id` 缓存，`time() + expires_in - 60` 提前过期
- 业务接口统一携带请求头 `access-token: <token>`（`AddRadarPlugin` 注入）
- `params['_access_token']` 外部注入优先（`ObtainClientTokenPlugin`），供用户自建共享缓存；进程内缓存在 PHP-FPM 高并发下会重复获取，官方频控 5 分钟 500 次

### 3.3 下单签名（`InvokePlugin`）

服务端不发 HTTP 请求，产出前端 `tt.requestOrder(data, byteAuthorization)` 所需的两个值：

```
待签串（五行，每行末尾 \n）:
  POST\n /requestOrder\n {timestamp秒}\n {nonce}\n {data JSON字符串}\n
byteAuthorization = Base64( SHA256withRSA(待签串, app_private_key) )
Header 值: SHA256-RSA2048 appid={app_id},nonce_str={nonce},timestamp={ts},key_version=1,signature={sig}
```

- `data` 为官方 camelCase 字段（`outOrderNo`/`totalAmount`/`skuList`/`orderEntrySchema` 等），SDK 透传组装 + JSON 序列化
- `setDirection(NoHttpRequestDirection)` 在 `$next` 前设置，`setDestination(new Collection(['data' => ..., 'byteAuthorization' => ...]))` 在 `$next` 后设置

### 3.4 trade_basic 业务接口

统一模式：`POST /api/trade_basic/v1/developer/<name>/`（**注意尾斜杠**）+ `access-token` 头鉴权，无请求签名，响应顶层 `err_no`/`err_msg`/`log_id`：

| 插件 | 端点 | 关键入参 |
|---|---|---|
| `Pay/QueryPlugin` | `order_query/` | `order_id` / `out_order_no` 二选一 |
| `Pay/QueryCpsPlugin` | `query_cps/` | `order_id` / `out_order_no` 二选一 |
| `Refund/RefundPlugin` | `refund_create/` | `order_id` + `out_refund_no` + `refund_reason[]` 必填；`notify_url` 可选（缺省注入配置 `notify_url`） |
| `Refund/QueryPlugin` | `refund_query/` | `refund_id` / `out_refund_no` / `order_id` 三选一（按订单查上限 50 条） |
| `Refund/AuditPlugin` | `refund_audit_callback/` | `refund_id` + `refund_audit_status`(1 同意/2 拒绝) 必填，拒绝时 `deny_message` 必填 |

### 3.5 回调（统一 `CallbackPlugin`）

三类回调统一 JSON body：`{"version": "3.0", "type": "payment|refund|pre_create_refund", "msg": "<JSON字符串>"}`，验签信息在**请求头** `Byte-Timestamp` / `Byte-Nonce-Str` / `Byte-Signature`：

```
验签串（三行，每行末尾 \n）: {Byte-Timestamp}\n{Byte-Nonce-Str}\n{原始body}\n
校验: SHA256withRSA(验签串, douyin_public_key) === Base64Decode(Byte-Signature)
```

- **必须用原始 body 字符串验签**，回调插件统一从 `params['_request']`（ServerRequestInterface）取原始 body 与回调头
- 入口签名：`callback(array|ServerRequestInterface|null $contents = null, ?array $params = null)`（满足 `ProviderInterface` 宽签名约束）；入参为 `ServerRequestInterface` 直接使用，`null` 取 `ServerRequest::fromGlobals()`，`array` 无回调头信息、抛 `PARAMS_CALLBACK_REQUEST_INVALID`
- 处理流程：验签 → 校验 body 顶层 `type` 为非空字符串 → 解析 `msg` JSON → payload/destination 均为 `Collection`（`msg` 内容），业务方按 `type` 分发处理
- 应答由业务方负责：普通回调响应 `success()`（HTTP 200 + `{"err_no":0,"err_tips":"success"}`）即可；`type=pre_create_refund`（退款申请回调）需业务方构造同步应答 `data.out_refund_no` + `order_entry_schema`，不响应平台会持续重试并卡单

### 3.6 响应校验

- `ResponsePlugin`：HTTP 2xx + 顶层 `err_no === 0`，异常消息取 `err_msg ?? err_tips`
- client_token 的 `data.error_code` 层级不同，由 `GetClientTokenPlugin` 自行校验

### 3.7 Provider 方法

| 方法 | 说明 |
|---|---|
| `mini($order)` | JSAPI 下单签名（`__call` → `MiniShortcut`） |
| `query($order)` | `_action`: `default/order`（order_query）、`cps`（query_cps）、`refund`（refund_query） |
| `refund($order)` | 创建退款；`_action=audit` 退款审核（内联 `RefundShortcut`） |
| `callback($contents = null, $params = null)` | 统一处理三类回调，内部强制 ServerRequestInterface |
| `success()` | `{"err_no":0,"err_tips":"success"}` |
| `cancel()` / `close()` | 抛 `PARAMS_METHOD_NOT_SUPPORTED`（新系统无此 API） |

- URL 常量：`URL = [normal → https://open.douyin.com, sandbox → https://open-sandbox.douyin.com, service → https://open.douyin.com]`
- 公共插件链完全由 Shortcut 组装（不使用 `mergeCommonPlugins()`，该方法已被 #1173 从全部 Provider 移除）

## 4. 参考文档

- 通用参数：<https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/common-param>
- 生成下单参数与签名：<https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/order/request-order-data-sign>
- 签名算法总览：<https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/signature-algorithm>
- 查询订单 / 查询 CPS / 支付结果回调：<https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/order/query-order> 、<https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/order/query-cps> 、<https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/order/notify-payment-result>
- 发起退款 / 查询退款 / 退款审核：<https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/refund/create-refund> 、<https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/refund/query-refund> 、<https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/refund/refund-audit>
- 退款申请回调 / 退款结果通知：<https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/locallife/general-ability/self-operated-trading/refund/refund-callback> 、<https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/refund/refund-notify>
- 获取 client_token：<https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/basic-abilities/interface-request-credential/non-user-authorization/get-client_token>
- 担保支付下线公告：<https://developer.open-douyin.com/forum/bulletin/post/674535ad67ccd35b42b71861> 、<https://developer.open-douyin.com/forum/synthesize/post/6769033bff7dbb42ad955679>
