---
feature: allinpay
status: in-progress
updated: 2026-09-10
branch: feat/allinpay
commits: 9fdd0f34..9fdd0f34
---

# 通联支付（Allinpay）Provider

## Report

## [S1] Problem

Issue #917 请求为 yansongda/pay 增加通联支付支持。当前 SDK 已支持支付宝、微信、银联、抖音、江苏银行、PayPal、Stripe、Airwallex，但缺少通联支付这一国内主流持牌机构。商户无法通过 `Pay::allinpay()` 完成统一支付、查询、退款、回调验签等主链路。

## [S2] Design

### 平台与网关

对齐通联**经典开放平台 apiweb**（与 go-pay 等主流实现一致）：

| Mode | Base URL |
|---|---|
| `MODE_NORMAL` | `https://vsp.allinpay.com/apiweb` |
| `MODE_SANDBOX` | `https://syb-test.allinpay.com/apiweb` |

不支持 `MODE_SERVICE`。集团/代理场景通过可选配置 `orgid` 表达，不占用 mode。

### 配置契约 `AllinpayConfig`

| 字段（snake） | 必填 | 说明 |
|---|---|---|
| `cusid` | 是 | 实际交易商户号 |
| `appid` | 是 | 平台分配 APPID |
| `orgid` | 否 | 集团/代理商商户号 |
| `mch_secret_key` | 是 | 商户 RSA 私钥（路径或 PEM/裸 base64，经 `CertManager::getPrivateCert`） |
| `allinpay_public_key` | 是 | 通联公钥（路径或 PEM，经 `CertManager::getPublicCert`） |
| `notify_url` | 否 | 支付结果通知 |
| `mode` | 否 | 默认 `MODE_NORMAL`；仅允许 NORMAL / SANDBOX |

校验失败抛 `InvalidConfigException`，错误码 `CONFIG_ALLINPAY_INVALID`。

### 签名

- 请求签名：对业务参数 + 公共参数（不含 `sign`）按 key 字典序拼成 `k=v&k=v`（跳过空值与数组），商户私钥 **RSA-SHA1** 签名后 base64。
- 响应/回调验签：JSON/form 中取 `sign`，其余字段同样拼串后用通联公钥 **RSA-SHA1** 验签；空签抛 `SIGN_EMPTY`，失败抛 `SIGN_ERROR`。
- 公共请求参数：`cusid`、`appid`、`orgid?`、`signtype=RSA`、`version`（默认 `11`，可被订单覆盖）、`randomstr`（20 位随机）、`sign`。
- 请求体：`application/x-www-form-urlencoded`（`Collection::query()`），响应 JSON（`JsonPacker` 解包）。对齐 Alipay V2「form 进 / JSON 出」模式。

### 业务 API 与 Shortcut

| Shortcut | Path | 必填业务字段 | 说明 |
|---|---|---|---|
| `unified` | `/unitorder/pay` | `reqsn`, `paytype` | 统一支付；命名为 `unified` 因 `pay()` 已被 `ProviderInterface` 占用 |
| `scan` | `/unitorder/scanqrpay` | `reqsn`, `authcode`, `terminfo` | 被扫（付款码） |
| `native` | `/unitorder/nativepay` | `reqsn`, `trxamt`, `expiretime` | 主扫，返回 `payinfo` 二维码串 |
| `query` | `/tranx/query` | `reqsn` 或 `trxid` | 交易查询 |
| `queryConfirm` | `/tranx/queryconfirm` | `reqsn` 或 `trxid` | 交易确认查询 |
| `refund` | `/tranx/refund` | `reqsn`, `trxamt`, `oldreqsn` 或 `oldtrxid` | 退款 |
| `cancel` | `/tranx/cancel` | `reqsn`, `trxamt`, `oldreqsn` 或 `oldtrxid` | 撤销 |
| `close` | `/unitorder/close` | `oldreqsn` 或 `oldtrxid` | 关单 |
| `nativeClose` | `/unitorder/closenative` | `oldreqsn` 或 `oldtrxid` | 主扫关单 |

`ProviderInterface` 映射：`query`/`cancel`/`close`/`refund` 委托对应 Shortcut 并派发 `MethodCalled`；支付走 `unified`/`scan`/`native` Shortcut；`callback` 本地验签；`success()` 返回 HTTP 200 纯文本 `success`。

### 插件管道

```
StartPlugin
→ 业务 Pay/Scan/Native/Query/... Plugin（写 _url 与业务 payload）
→ AddPayloadSignPlugin（RSA-SHA1）
→ AddRadarPlugin（POST form-urlencoded）
→ VerifySignaturePlugin（响应 JSON 验签）
→ ResponsePlugin（retcode === SUCCESS，否则 InvalidResponseException）
→ ParserPlugin（JsonPacker → Collection）
```

回调：

```
CallbackPlugin（从 Collection/ServerRequest 取 form → 验签 → NoHttpRequestDirection）
```

### 调用示例（目标 API）

```php
Pay::allinpay()->unified([
    'reqsn' => 'order-1001',
    'trxamt' => 1, // 分
    'paytype' => 'W02',
    'body' => '商品',
]);

Pay::allinpay()->query(['reqsn' => 'order-1001']);
Pay::allinpay()->refund(['reqsn' => 'r-1', 'trxamt' => 1, 'oldreqsn' => 'order-1001']);
Pay::allinpay()->callback(); // 本地验签
```

### 测试边界

- 单测使用 Mock `HttpClientInterface` 注入构造好的签名响应/回调（参考 JsbTest）。
- 覆盖：Config 校验、URL 模式切换、签名生成/验签成功与失败、九个 Shortcut 管道、callback 验签、`retcode != SUCCESS` 抛错、未知 shortcut 抛 `PARAMS_SHORTCUT_INVALID`。
- 生成一对测试用 RSA 密钥写入 `tests/Cert/allinpay*`，响应签名用对应私钥生成以便验签通过。

## [S3] Out of Scope

- 通商云新开放平台协议、SM2 签名。
- 服务商分账、会员、营销类非支付接口。
- 文档站（pay.yansongda.cn）页面更新（可另开 issue）。
- 真实沙箱联调（需商户凭证）。

## Tasks

- [ ] T1: AllinpayConfig + Exception 常量 + Config 注册 — acceptance: `Pay::allinpay()` 可取到配置；缺 `cusid`/`appid`/`mch_secret_key`/`allinpay_public_key` 时抛 `CONFIG_ALLINPAY_INVALID` (covers: S2)
- [ ] T2: AllinpayTrait + Provider + Service + Pay 常量 — acceptance: `Pay::PROVIDER_ALLINPAY`、`Pay::allinpay()` 可用；query/cancel/close/refund 派发事件并走 Shortcut (covers: S2)
- [ ] T3: 通用插件 Start/Sign/Radar/Verify/Response/Callback — acceptance: 管道可对 form 请求签名、验 JSON 响应签、`retcode!=SUCCESS` 抛错、callback 本地验签 (covers: S2)
- [ ] T4: 九个业务 Plugin + Shortcut — acceptance: `unified/scan/native/query/queryConfirm/refund/cancel/close/nativeClose` 可调用且 `_url`/必填字段正确 (covers: S2; depends: T3)
- [ ] T5: 单元测试 + 测试证书 — acceptance: `vendor/bin/phpunit --filter Allinpay` 全绿，覆盖配置/签名/回调/业务码错误 (covers: S2; depends: T1, T2, T3, T4)
