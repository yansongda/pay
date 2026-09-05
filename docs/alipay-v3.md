# 支付宝 V3 API 支持 · 技术设计文档

> **状态**：已实现（阶段一，接口级自动分流架构，V3 仅证书模式）
> **对照实现**：`feat/alipay-v3` 分支，基线 test 1469 / assertions 3534 全绿

---

## 1. 背景与目标

支付宝官方已推出 OpenAPI V3（RESTful `/v3/` 路径、纯 JSON、HTTP 头签名 `ALIPAY-SHA256withRSA`），与 V2（网关签名 + form 表单）的签名、传参、回调验签机制完全不同。

**最终架构决策**（经过两轮迭代，最终采用接口级分流）：

- **接口级自动分流**：调用某接口时，SDK 已实现 V3 版本的直接走 V3 最新版；未实现的自动回落 V2。**不引入 `version` 租户配置**，调用代码零改动
- **单一配置类**：V2/V3 共用一套租户配置与证书体系，V3 **仅支持证书模式**（V2 无公钥模式，证书字段天然共用，存量用户零配置变更）
- **存量为零影响**：`web/h5/app/mini/transfer` 走 V2 的行为完全不变

## 2. 整体架构

```
调用入口（无任何版本配置）
        │
Pay::alipay()
        │
Provider\Alipay::__call(shortcut)
        │
        ├─ shortcut ∈ V3_SHORTCUTS ──► Shortcut\Alipay\V3\{X}Shortcut   （V3 管道）
        └─ 其余 shortcut            ──► Shortcut\Alipay\{X}Shortcut     （V2 管道，不动）

V3 管道（以 scan 为例）：
StartPlugin(Artful) → Pay\PrecreatePlugin(设 _url/_method/payload)
→ AddPayloadBodyPlugin(Artful) → AddPayloadSignaturePlugin(V3) → AddRadarPlugin(V3)
→ [HTTP openapi.alipay.com/v3/...]
→ ResponsePlugin(V3) → VerifySignaturePlugin(V3) → ParserPlugin(Artful)

V3_SHORTCUTS = ['pos', 'scan', 'query', 'refund', 'cancel', 'close']
```

`V3_SHORTCUTS` 常量为接口级分流唯一依据；`query/cancel/close/refund` 等 ProviderInterface 方法经 `__call` 自动分流。**异步通知不走此分流**：无论通知来自 V2 还是 V3 接口，报文均为 V2 form 格式，由统一的回调插件处理（见 §5）。

## 3. 配置设计

单一 `Yansongda\Pay\Config\AlipayConfig`（具体类，无版本概念），V2/V3 完全共用：

| 字段 | 类型 | 说明 |
|---|---|---|
| `app_id` | string，必填 | 支付宝分配的 app_id |
| `app_secret_cert` | string，必填 | 应用私钥（字符串或路径），V2/V3 请求签名共用 |
| `app_public_cert_path` | string，必填 | 应用公钥证书路径，V2/V3 计算 `app_cert_sn` 共用 |
| `alipay_public_cert_path` | string，必填 | 支付宝公钥证书路径，V2/V3 验签共用 |
| `alipay_root_cert_path` | string | 支付宝根证书路径；**仅 V2** 计算 `root_cert_sn` 使用（V3 协议无 `root-cert-sn`），V2 管道调用时懒校验 |
| `notify_url` / `return_url` / `app_auth_token` / `service_provider_id` / `mode` | 选填 | 含义与 V2 一致 |

校验规则：构造时强制 `appId + appSecretCert + appPublicCertPath + alipayPublicCertPath`（两管道均依赖）；`alipayRootCertPath` 懒校验（V2 `StartPlugin` 计算 `root_cert_sn` 时要求）。

已移除的配置项：`version`（接口级分流取代）、`alipay_public_key`（V3 不支持公钥模式）。`Config` 装配为 `new AlipayConfig($config, $tenant)`。

## 4. 签名/验签契约（对照官方 SDK `alipay-sdk-php-all` `v3/src/` 一手核实）

### 4.1 请求签名

```
authString = "app_id={id},app_cert_sn={sn},nonce={uuidV4},timestamp={毫秒13位}"
signContent = authString + "\n" + METHOD + "\n" + "/v3/alipay/...(含query，不含域名)" + "\n"
            + body + "\n"（空 body 保留空行）
            + appAuthToken + "\n"（仅配置了 app_auth_token 时，第 5 行）
sign = base64(SHA256withRSA(appSecretCert, signContent))
Authorization: "ALIPAY-SHA256withRSA " + authString + ",sign=" + sign
```

- 组串顺序：`app_id → app_cert_sn → nonce → timestamp`（证书模式下 `app_cert_sn` 无条件携带）
- **timestamp 为毫秒**（13 位，对齐官方 `getCurrentMilis()`），与微信 V3 的秒级不同
- 通用 header：`Content-Type: application/json`、`alipay-request-id`（UUID v4，官方无条件携带）、`alipay-app-auth-token`（第三方授权时）
- **V3 无 `alipay-root-cert-sn` 机制**
- `nonce` 与时间戳生成逻辑已内联进 `getAlipayV3Authorization()`（不设独立私有方法）

### 4.2 同步响应验签

- 组串：`{alipay-timestamp}\n{alipay-nonce}\n{响应body}\n`（末尾必带 `\n`，`alipay-timestamp` 为毫秒 13 位）
- 取 header `alipay-signature` 验签；公钥来自租户 `alipay_public_cert_path` 证书
- `alipay-sn` 与本地证书 SN **无条件严格匹配**（官方会回落缓存第一个公钥，SDK 有意更严格，不匹配/缺失一律抛 `InvalidSignException` 提示更新证书）
- 时间戳 **±300 秒**时效校验（强制路径无条件校验；非强制路径有签才校验）
- 验签策略对齐官方：**HTTP 200 强制验签；其余响应存在 `alipay-signature` 时验签（防篡改）**，无签名直接放行进入错误处理（`ResponsePlugin` 抛业务异常）；不做证书自动轮换
- 验签方法统一为 `AlipayTrait::verifyAlipaySign()`（纯证书，V2 同步验签/应用回调/异步通知/V3 同步验签共用；组串由各调用方负责）

## 5. 回调设计（V2/V3 统一）

支付宝 trade 异步通知经官方文档与 SDK 源码双源互证：**无论触发通知的接口走 V2 还是 V3 管道，通知均为 V2 form 参数格式**（非 JSON body + header 签名）。因此回调不做版本分流：

- 统一使用 `Yansongda\Pay\Plugin\Alipay\CallbackPlugin`（由 `V2\CallbackPlugin` 上移；原 `V3\CallbackPlugin` 已删除）：除去 `sign`/`sign_type` 后按字典序组串 + RSA2 验签，**验签无条件强制、不可关闭**
- `Provider\Alipay::callback()` 统一走 `getCallbackParams()`：支持 `null`（fromGlobals）、`ServerRequestInterface`（GET 取 queryParams / POST 取 parsedBody）、纯数组、`['body' => ..., 'headers' => ...]`（webhook 转发形态，自动 parse_str）
- `CallbackReceived` 事件载荷统一为**解析后的通知参数数组**
- 应答为字面量 `success`（`Pay::alipay()->success()`）
- 应用回调 `appCallback()` 为 APP 支付（V2 管道）专属，恒走 V2 流程

## 6. 接口清单（阶段一）

| Shortcut | 方法/路径 | 说明 |
|---|---|---|
| `pos()` | POST `/v3/alipay/trade/pay` | 付款码支付（被扫码） |
| `scan()` | POST `/v3/alipay/trade/precreate` | 扫码支付（预创建，主动扫） |
| `query()` | POST `/v3/alipay/trade/query` | 交易查询 |
| `refund()` | POST `/v3/alipay/trade/refund` | 交易退款 |
| `cancel()` | POST `/v3/alipay/trade/cancel` | 交易撤销 |
| `close()` | POST `/v3/alipay/trade/close` | 交易关闭 |

- 订单参数与官方 V3 请求体一致（snake_case 直传，无 `biz_content` 包装），`notify_url` 订单参数优先、回落租户配置
- 响应返回 `Collection`，字段与官方 V3 响应体一致（无包裹层）；非 2xx + 业务错误体由 `ResponsePlugin` 统一抛出
- 网关域名：`Provider\Alipay::URL`（V2/V3 共用纯域名，V2 拼 `gateway.do?charset=utf-8`）；沙箱模式 V3 走 `V3_SANDBOX_URL = http://openapi.sandbox.dl.alipaydev.com`（与 V2 沙箱域名不同）
- 第三方授权（服务模式）：订单参数 `'_app_auth_token'` 或租户配置 `app_auth_token`，SDK 自动放入请求头并参与签名组串

## 7. 文件结构（当前实现）

```
src/
├── Config/AlipayConfig.php                  [单一配置类，V2/V3 共用]
├── Contract/ProviderConfigInterface.php     [自 Config/ 移入，BC]
├── Provider/Alipay.php                      [V3_SHORTCUTS 接口级分流；callback 统一]
├── Plugin/Alipay/
│   ├── CallbackPlugin.php                   [V2/V3 统一异步通知验签（自 V2/ 上移）]
│   ├── V2/…                                 [V2 管道，页面类接口等，不动]
│   └── V3/
│       ├── AddPayloadSignaturePlugin.php    # Authorization 组串签名
│       ├── AddRadarPlugin.php               # JSON PSR-7 Request + 通用 header
│       ├── VerifySignaturePlugin.php        # 同步验签 + alipay-sn 匹配 + 时间戳校验
│       ├── ResponsePlugin.php               # 非 2xx 业务错误体抛异常
│       └── Pay/{Pos,Precreate,Query,Refund,Cancel,Close}Plugin.php
├── Shortcut/Alipay/V3/                      [六个 V3 Shortcut]
└── Traits/AlipayTrait.php                   [getAlipayV3Url/getAlipayV3Authorization/verifyAlipaySign 等]

tests/
├── Cert/alipay-v3/                          [V3 自签测试证书（证书模式全链路自签自验）]
├── Plugin/Alipay/{CallbackPluginTest,V3/…}Test.php
├── Config/AlipayConfigTest.php
└── Shortcut/Alipay/V3/…
```

## 8. 兼容性（BC 清单）

1. **接口行为变化**：`pos/scan/query/refund/cancel/close` 自动走 V3，响应字段为官方 V3 格式（与 V2 响应可能存在差异）；沙箱模式走 V3 沙箱网关
2. **配置项移除**：`version` 不再生效；`alipay_public_key` 移除（V3 仅证书模式）
3. **配置类合并**：`AlipayV2Config`/`AlipayV3Config` 删除，`AlipayConfig` 变具体类；必填收窄为四件，`alipayRootCertPath` 懒校验
4. **namespace 变更**：`ProviderConfigInterface` 迁至 `Yansongda\Pay\Contract`
5. **事件载荷**：`CallbackReceived` 统一为通知参数数组
6. 零新 Composer 依赖，PHP >= 8.2 不变

## 9. 阶段二规划（本次未实现）

- 页面类接口 V3 化（`web/h5/app/mini`，当前自动回落 V2）
- `transfer` V3 化、AES-128-CBC 通知解密（`encrypt_key`，文档已提示勿开通加密能力）
- V3 公钥模式（`alipay_public_key`，如有需求按需支持）
- 证书自动轮换（`alipay-sn` 变化时自动拉取新证书）

## 附录：契约来源

主要来源：

- 签名：https://opendocs.alipay.com/common/057k53
- 验签：https://opendocs.alipay.com/common/02mse7
- V3 协议简介：https://opendocs.alipay.com/open-v3/054kaq
- 官方 PHP SDK：https://github.com/alipay/alipay-sdk-php-all （V3 签名核心：`v3/src/Util/AlipayConfigUtil.php`）
