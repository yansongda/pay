# Apple（苹果支付）Provider 技术设计方案

> **时间**：2026-09-20
> **作者**：DeepSeek V4 + yansongda
> **状态**：经过人工审核确认（v2：已纳入 plan-reviewer 独立审查意见的逐条复核结论）

---

## 1. 背景与问题

### 1.1 背景

Issue #597 社区请求支持苹果支付。仓库现有 10 个 Provider，新增 Provider 流程成熟。Apple Pay 与国内支付（支付宝/微信）有本质差异：**没有服务端发起支付接口**，支付由 iOS 设备/钱包发起，服务端职责是「验签解密支付令牌 + 交易/退款查询 + 回调验签」。骨架参考**微信/支付宝**（已过大量生产验证）而非 Stripe。

### 1.2 问题

1. **验签解密是本地密码学操作**：Apple Pay 支付令牌（PKCS#7 detached 签名 + ECDH + AES-GCM 加密）需服务端本地验证，不走 HTTP API，与现有"业务插件调 API"模式不同
2. **无服务端主动退款接口**：退款只能由客户发起，服务端仅能查退款历史（Get Refund History）——`refund()` 无法映射为"调退款 API"
3. **回调机制不同**：App Store Server Notifications V2 是 JWS（x5c 证书链 + ES256），与现有 HMAC（Stripe）/RSA（支付宝）验签机制均不同，且内嵌 `signedTransactionInfo`/`signedRenewalInfo` 二级 JWS
4. **API 认证不同**：App Store Server API 用 App Store Connect API Key 签 ES256 JWT（`kid/iss/iat/exp/aud/bid` claims），非静态密钥
5. **PHP 无官方 SDK**：Apple 只提供 Swift/Java/Python/Node 官方库；PHP 侧仅有 etsy C 扩展（RSA_v1 支持不完整）与 PayU 纯 PHP 包（依赖 openssl CLI + symfony/process，不适用本仓库）
6. **无测试资源**：需 Apple Developer 付费会员（$99/年）+ 沙盒环境才能真实联调，owner 已明确无此资源

### 1.3 目标

- **零新增依赖**：全部密码学（PKCS#7 验签、ECDH、KDF、AES-GCM、ES256）基于已有 ext-openssl（PHP ≥ 8.2 已要求）实现
- **骨架对齐微信/支付宝**：Provider/Shortcut/Plugin/Trait/Config/注册链/测试/文档均仿照微信支付宝结构（生产验证过的模式）
- **多租户**：`_config` 租户切换与现有 Provider 完全一致
- **可测试**：无真实环境也能通过「本地自造密钥链模拟 Apple 签名」覆盖全部代码路径；测试 fixture 证书提交到 `tests/Cert/apple/`（对齐仓库 `tests/Cert/` 惯例），运行时不依赖外部工具
- **契约分级标注**：Apple 协议细节标注「已验证（官方文档）」或「推断（未实测）」

---

## 2. 整体方案

### 2.1 核心思路

**以微信/支付宝为骨架，新增 `Apple` Provider（`Pay::apple()`），一期覆盖 Apple 服务端三大能力：支付令牌验签解密（本地，`payToken()`）、App Store Server API（`query()`/`refund()`，ES256 JWT 认证）、App Store Server Notifications V2（`callback()`，JWS 验签）。** 密码学实现拆至 `src/Crypto/Apple/`（`Cryptor`/`TokenVerifier`/`JwsVerifier`，零依赖），`Traits/AppleTrait.php` 作为对外唯一 Trait 门面，内置证书存放 `src/Certificate/`（公开证书，可配置覆盖）。

### 2.2 架构图

```
┌──────────────────────────── 调用方（业务服务端） ────────────────────────────┐
│                                                                              │
│  Pay::apple()->payToken(['token' => $token])       → 本地验签解密（不发 HTTP） │
│  Pay::apple()->merchantSession([...])              → 网页网关代理（P1）        │
│  Pay::apple()->query($order)                       → App Store API（HTTP+JWT） │
│  Pay::apple()->refund($order)                      → 退款历史查询（HTTP+JWT）  │
│  Pay::apple()->callback($request)                  → 通知 JWS 验签（本地）     │
└──────────────────────────────────┬───────────────────────────────────────────┘
                                   │
        ┌──────────────────────────┼──────────────────────────────┐
        ▼                          ▼                              ▼
┌─────────────────┐      ┌─────────────────────┐      ┌──────────────────────┐
│ Traits/AppleTrait│      │ Plugin/Apple        │      │ Certificate/         │
│ getAppleUrl      │      │ AddRadarPlugin      │      │ AppleRootCA-G3.pem   │
│ verifyAppleToken │◄─────│ Pay/PayTokenPlugin  │      │ AppleAAICAG3.pem     │
│  ├ ASN.1 解析    │      │ Pay/MerchantSession │      │ (内置公开证书,可覆盖) │
│  ├ 链验证+OID    │      │ Pay/Query×3         │      └──────────────────────┘
│  └ ECDH+KDF+GCM  │      │ Pay/RefundPlugin    │
│ verifyAppleJws   │◄─────│ Pay/CallbackPlugin  │
│  ├ x5c 链验证    │      │ ResponsePlugin      │
│  └ ES256 验签    │      └─────────────────────┘
│ generateAppleJwt │◄──────── (AddRadarPlugin 取 JWT 认证头)
│  └ ES256 签发    │
└─────────────────┘
```

### 2.3 文件结构（对齐微信/支付宝组织）

```
src/
├── Provider/Apple.php                    ← 新增（骨架对齐 Wechat：URL 三模式常量 + __call + callback）
├── Service/AppleServiceProvider.php      ← 新增
├── Config/AppleConfig.php                ← 新增（对齐 WechatConfig：属性+getter/setter+validateRequired）
├── Traits/AppleTrait.php                 ← 新增（对外唯一 Trait：URL 组装 + ES256 JWT + 密码学入口）
├── Enum/Apple/SubscriptionStatus.php     ← 新增（订阅 status 枚举：ACTIVE..REVOKED）
├── Crypto/Apple/Cryptor.php              ← 新增（ASN.1/PEM/base64url/ECDSA 转换 + 证书链验证）
├── Crypto/Apple/TokenVerifier.php        ← 新增（payToken 验签解密：PKCS#7/ECDH-KDF/AES-GCM）
├── Crypto/Apple/JwsVerifier.php          ← 新增（Notifications V2 JWS 验签 + 归属校验）
├── Action/AppleAction.php                ← 新增（QUERY_TRANSACTION/HISTORY/SUBSCRIPTIONS）
├── Certificate/                          ← 新增（内置公开证书目录）
│   ├── AppleRootCA-G3.pem                ← 新增（Apple 根证书，公开）
│   └── AppleAAICAG3.pem                  ← 新增（Apple 中间 CA，公开）
├── Plugin/Apple/                         ← 无版本目录，按业务域（对齐 Wechat：根放共用插件）
│   ├── AddRadarPlugin.php                ← 新增（Bearer JWT + JSON body 组装）
│   ├── ResponsePlugin.php                ← 新增（2xx 校验）
│   └── Pay/
│       ├── PayTokenPlugin.php            ← 新增（本地验签解密，NoHttpRequestDirection）
│       ├── MerchantSessionPlugin.php     ← 新增（网关代理，POST validation_url）
│       ├── QueryPlugin.php               ← 新增（GET /inApps/v1/transactions/{id}）
│       ├── QueryHistoryPlugin.php        ← 新增（GET /inApps/v2/history/{id}）
│       ├── QuerySubscriptionsPlugin.php  ← 新增（GET /inApps/v1/subscriptions/{id}?status=1&status=4）
│       ├── RefundPlugin.php              ← 新增（GET /inApps/v2/refund/lookup/{id}）
│       └── CallbackPlugin.php            ← 新增（JWS 验签 + 内嵌交易 JWS 二级解析）
├── Shortcut/Apple/
│   ├── PayTokenShortcut.php              ← 新增（链：[StartPlugin, PayTokenPlugin]，无 ParserPlugin——本地操作）
│   ├── MerchantSessionShortcut.php       ← 新增（链：[StartPlugin, MerchantSessionPlugin, AddRadarPlugin, ResponsePlugin, ParserPlugin]）
│   ├── QueryShortcut.php                 ← 新增（_action 分发；链同 MerchantSession 去业务插件）
│   └── RefundShortcut.php                ← 新增（同上）
├── Pay.php                               ← 修改（PROVIDER_APPLE 常量 + @method + $providers）
├── Config.php                            ← 修改（PROVIDERS 列表 + match 分支）
└── Exception/Exception.php               ← 修改（PARAMS_APPLE_*/CONFIG_APPLE_INVALID/DECRYPT_APPLE_*）
tests/
├── Cert/apple/                           ← 新增（fixture 证书与密钥，对齐 tests/Cert/ 惯例）
├── Support/Apple/                        ← 新增（运行时 token/JWS 构造器）
├── Provider/AppleTest.php、Config/AppleConfigTest.php、Traits/AppleTraitTest.php
├── Plugin/Apple/**、Shortcut/Apple/**
web/docs/v3/apple/{pay,query,refund,callback,response,all}.md（6 篇，无 cancel/close，对齐 Jsb 先例）
web/docs/v3/quick-start/apple.md + web/.vitepress/sidebar/v3.js（修改）
CHANGELOG.md、README.md（修改）
```

---

## 3. 详细设计

### 3.1 Provider 方法与快捷方式（骨架对齐 Wechat.php，已验证源码）

| 方法 | 行为 | 微信对照 |
|---|---|---|
| `payToken(['token' => $token])` | **本地**验签+解密支付令牌，返回解密后卡数据 Collection，不发 HTTP。方法入参必须是数组（`__call → Artful::shortcut(string, array)` 契约）；`token` 键的值支持三种形态：**JSON 字符串、关联数组、`Collection`**（均含 `data`/`header`/`signature`/`version`），Trait 层统一归一化处理 | 无直接对照（语义上接近"回调验签"的加强版——不仅验真还解密卡数据） |
| `merchantSession(['validation_url' => ..., '_http' => ['cert' => [...]]])` | 代理 POST Apple Pay 网页网关；`validation_url` 为前端 `onvalidatemerchant` 事件拿到的一次性 URL（5 分钟过期）；TLS 双向认证证书经 `_http` 透传（**P1 可裁剪**） | 无对照 |
| `query(['transaction_id' => ...])` | `_action` 分发：`transaction`（默认）/`history`/`subscriptions` | Wechat QueryShortcut |
| `refund(['transaction_id' => ...])` | Get Refund History（Apple 无服务端主动退款，语义为退款历史查询） | Wechat RefundShortcut |
| `callback($contents, $params)` | `getCallbackParams()` 构造 ServerRequest → CallbackPlugin JWS 验签 + 二级解析 | Wechat::callback（已验证同款骨架） |
| `cancel()/close()` | 抛 `PARAMS_METHOD_NOT_SUPPORTED`（Apple 无取消/关单 API） | Wechat 有 API，Apple 无 |
| `success()` | 200 JSON `{"result":"success"}` | — |
| `pay($plugins, $params)` | 接口原生自定义插件链 | Wechat::pay |

- Provider 命名：`Pay::PROVIDER_APPLE = 'apple'`，调用 `Pay::apple()`，`@method` 注解 IDE 提示
- URL 三模式常量（对齐 Wechat::URL，NORMAL/SERVICE 同址）：

```php
public const URL = [
    Pay::MODE_NORMAL => 'https://api.storekit.apple.com',
    Pay::MODE_SANDBOX => 'https://api.storekit-sandbox.apple.com',
    Pay::MODE_SERVICE => 'https://api.storekit.apple.com',
];
```

（已验证：Apple changelog 2026/05 推荐新域名，旧域名仍支持）

- **Shortcut 插件链**（对齐 Wechat AppShortcut，去掉签名/验签插件；**不使用 `AddPayloadBodyPlugin`**——Apple 请求体由插件直接构造 `_body`，避免 AddPayloadBodyPlugin 覆盖业务 body）：

```
HTTP 类（MerchantSession/Query/Refund）：[StartPlugin, 业务Plugin, AddRadarPlugin, ResponsePlugin, ParserPlugin]
本地类（PayToken）：[StartPlugin, PayTokenPlugin] —— 无 ParserPlugin（ParserPlugin 要求 destination 为 null|ResponseInterface，本地操作会产生 Collection；对齐 Stripe callback 无 ParserPlugin 先例）
Callback（Provider 内置）：pay([CallbackPlugin::class], ['_request' => ..., '_params' => ...]) —— 无 ParserPlugin，artful 直接返回 destination Collection
```

### 3.2 配置设计（AppleConfig）

```php
// tests/TestCase.php 测试配置示例（引用提交到 tests/Cert/apple/ 的 fixture）
'apple' => [
    'default' => [
        'merchant_id' => 'merchant.com.yansongda.pay',
        'payment_processing_cert' => __DIR__.'/Cert/apple/merchant.pem',
        'apple_root_ca' => __DIR__.'/Cert/apple/token-root.crt',
        'issuer_id' => '69a6de87-test-issuer-id',
        'bundle_id' => 'com.yansongda.pay.test',
        'api_key_id' => 'X5D4K9J2Q1',
        'api_private_key' => __DIR__.'/Cert/apple/api.key',
        'mode' => Pay::MODE_SANDBOX,
    ],
],
```

| 字段 | 类型 | 必填 | 用途 | 契约等级 |
|---|---|---|---|---|
| merchant_id | string | 是 | KDF PartyVInfo = SHA-256(merchant_id) | 已验证（官方文档+三实现交叉） |
| payment_processing_cert | string | 是 | 支付处理证书 PEM（**路径或 PKCS#8 内容**，含私钥）；P12/SEC1 由文档指引转换（`openssl pkcs8 -topk8 -nocrypt`）。注：`CertManager::getPrivateCert` 的内容分支仅识别 PKCS#8 头，SEC1/RSA 传统格式内容会被二次包装，推荐传路径 | 已验证（官方文档） |
| payment_processing_cert_passphrase | ?string | 否 | 私钥口令 | — |
| apple_root_ca / apple_intermediate_ca | ?string | 否 | 覆盖内置 `src/Certificate/` 信任锚与中间 CA（路径或 PEM 内容）。**PKCS#7 与 x5c 链内的 intermediate 优先取自报文本身，配置项为兜底** | 已验证（官方 CA 页） |
| issuer_id / bundle_id / api_key_id / api_private_key | string | 仅 API 功能 | App Store Server API 的 ES256 JWT 认证（bid claim = bundle_id） | 已验证（官方文档） |
| notify_url | ?string | 否 | 文档用，SDK 不主动上报 | — |
| mode | int | 是 | 取值 `Pay::MODE_NORMAL`/`Pay::MODE_SANDBOX`/`Pay::MODE_SERVICE`（**配置示例与文档一律写常量，不出现裸数字**） | — |

`validateRequired()`：`merchant_id` + `payment_processing_cert` 必填；若配置任一项 API 密钥字段则要求四项齐全，否则抛 `CONFIG_APPLE_INVALID`。支持三模式。

### 3.3 支付令牌验签解密（TokenVerifier 核心；对齐 WechatTrait 风格，单文件过大故把实现下沉为 `src/Crypto/Apple/` 下的最终类，`AppleTrait` 仅保留门面）

**Trait 对外 API**（public static，插件内 `self::xxx()` 调用）：

| 方法 | 说明 |
|---|---|
| `getAppleUrl(AppleConfig, ?Collection): string` | URL 构建：`_url` 以 `http` 开头直通（merchantSession 用），否则拼 `Apple::URL[mode]`（插件 `_url` 统一以 `/inApps/...` 前导斜杠书写） |
| `verifyAppleToken(Collection\|array\|string $token, array $params = []): Collection` | 验签+解密支付令牌（配置经容器按租户取；三形态归一化：string 做 `json_decode`、array/Collection 转数组后统一校验） |
| `verifyAppleJws(string $signedPayload, array $params = []): array` | 通知 JWS 验签+解码（含二级内嵌 JWS） |
| `generateAppleJwt(array $params = []): string` | App Store Connect ES256 JWT 签发 |
| private static | `parseAppleAsn1()`（ASN.1 最小解析，返回含完整 DER 节点重构能力）、`verifyAppleTokenSignature()`、`verifyAppleChain()`、`deriveAppleSymmetricKey()`、`decryptAppleData()`、`derToRawApple()`、`base64UrlEncode/Decode` |

**验签失败一律抛 `InvalidSignException`（`SIGN_ERROR`/`SIGN_EMPTY`）——与仓库所有 Provider 的验签异常惯例一致（已核实 `src/Exception/InvalidSignException.php` 与各 Trait 用法）**；配置缺失抛 `InvalidConfigException(CONFIG_APPLE_INVALID)`；token 结构非法抛 `InvalidParamsException(PARAMS_APPLE_TOKEN_INVALID)`。

**验签流程**（EC_v1；RSA_v1 仅拼接字段与签名算法不同）：

```
verifyAppleToken(token, config):
  1. content = base64_decode(ephemeralPublicKey) ∥ base64_decode(data) ∥ hex2bin(transactionId)
     [ ∥ hex2bin(applicationData) 当且仅当存在且非空（官方 hex 口径） ]
  2. p7 = parseAppleAsn1(base64_decode(signature))   # 提取 certificates[]、signerInfo.signedAttrs/signature
  3. 链验证: leaf → intermediate → apple_root_ca（openssl_x509_verify 逐级；intermediate 优先取报文内证书，缺失时用配置兜底）
     OID 检查: leaf 必须含 1.2.840.113635.100.6.29 / intermediate 必须含 1.2.840.113635.100.6.2.14
  4. pubkeyHash 校验: SHA-256(商户证书公钥 DER) == base64_decode(header.publicKeyHash)
  5. messageDigest 校验: signedAttrs 内 1.2.840.113549.1.9.4 属性值 == SHA-256(content)
  6. 签名验证: openssl_verify(signedAttrs 重构 DER（首字节 0xA0→0x31）, signature, leafPubKey, SHA256)
  7. signingTime 检查: signedAttrs 内 1.2.840.113549.1.9.5 与当前时间差 ≤ 300s（防重放）
  8. Z = openssl_pkey_derive(ephemeralPubKey, merchantPrivKey)        # ECDH
  9. key = SHA256(0x00000001 ∥ Z ∥ 0x0D"id-aes256-GCM""Apple" ∥ SHA256(merchant_id))
 10. AES-256-GCM: iv = 16×0x00, tag = data[-16:], 无 AAD
 11. 返回明文 JSON（DPAN/expirationDate/currencyCode/transactionAmount/...）
```

**关键实现约束（审查后补充）**：
- **signedAttrs 验签原文**：ASN.1 解析需能还原 signedAttrs 节点的**完整 DER（含 tag+length）**，然后将首字节 `0xA0` 替换为 `0x31`（RFC 5652 §5.4；PayU `Asn1Wrapper::getSignedAttributes()` 同做法）
- **hex 字段防护**：`hex2bin()` 对非法 hex 在 PHP 8 抛 `ValueError`——`transactionId`/`applicationData` 需先校验（`ctype_xdigit` + 偶数长度），非法则抛 `PARAMS_APPLE_TOKEN_INVALID`
- **signingTime 缺失策略**：signedAttrs 中找不到 OID `1.2.840.113549.1.9.5` 时同样按**验签失败（`SIGN_ERROR`）**处理（安全优先；真实 Apple token 含该属性——PayU 生产实现依赖此行为；fixture 由 `openssl_pkcs7_sign` 默认生成该属性，缺失分支不可测）
- **DER→PEM 包装**：自实现 `pemWrap(string $der, string $label): string`（`'-----BEGIN '.$label.'-----'` + `chunk_split(base64_encode($der), 64, "\n")` + END 行），用于证书/公钥送入 openssl 系列函数
- **DER→raw（JWT/JWS 用）**：解析 `SEQUENCE{INTEGER r, INTEGER s}` 时先剥离防负前导 `0x00`，再各左补零到 32 字节（处理 33 字节 INTEGER 场景）
- **RSA_v1 版本约束**：OAEP-SHA256 解密需 `openssl_private_decrypt(..., OPENSSL_PKCS1_OAEP_PADDING, 'sha256')`，该 `digest_algo` 参数 **PHP 8.5 才引入**（PHP 8.2–8.4 硬编码 OAEP SHA-1）。实现必须：`PHP_VERSION_ID < 80500` 时抛明确异常（`DECRYPT_APPLE_FAILED`，文案说明需 PHP ≥ 8.5 且 EC_v1 无此限制），禁止静默使用 SHA-1
- **AES-GCM 解密**：`openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv16, $tag, '')`

**KDF 已验证**（Apple 官方 Restoring the Symmetric Key + etsy/PayU/samcorcos 交叉一致）：counter 为 4 字节大端 `0x00000001`；AlgorithmID 是「长度前缀 0x0D + ASCII `id-aes256-GCM`」非 DER 编码；PartyUInfo = `Apple`；PartyVInfo = SHA-256(明文商户 ID)。

**内置证书读取**：默认 `__DIR__.'/../Certificate/AppleRootCA-G3.pem'`（及 AAICA），配置 `apple_root_ca`/`apple_intermediate_ca` 覆盖（路径或 PEM 内容，经 `CertManager::getPublicCert()`）。

### 3.4 App Store Server API（query/refund）

**认证**：ES256 JWT（可缓存至 exp），`Authorization: Bearer <jwt>`：

```json
{ "alg": "ES256", "kid": "<api_key_id>", "typ": "JWT" }
{ "iss": "<issuer_id>", "iat": <now>, "exp": <now+300>, "aud": "appstoreconnect-v1", "bid": "<bundle_id>" }
```

`generateAppleJwt` 自实现：`openssl_sign`（SHA-256 + P-256）输出 DER，经 `derToRawApple` 转 r∥s 后 base64url。**无真实 Apple 响应实测，响应字段契约标「推断（官方文档）」**。

**接口映射**（均 `GET`，URL 以 `{URL}/inApps/` 为前缀，插件内 `_url` 统一写前导斜杠形式）：

| 方法/_action | 端点 | 用途 |
|---|---|---|
| query 默认 / `transaction` | `GET /inApps/v1/transactions/{transactionId}` | 单笔交易 |
| query `history` | `GET /inApps/v2/history/{anyTransactionId}` | 交易历史（分页 revision） |
| query `subscriptions` | `GET /inApps/v1/subscriptions/{anyTransactionId}?status=1&status=4` | 订阅状态（重复 query 参数，非数组形式） |
| refund（唯一动作） | `GET /inApps/v2/refund/lookup/{anyTransactionId}` | 退款历史（`signedTransactions` 内嵌 JWS 原样返回，业务方可自行验签） |

**GET 请求的 query 语义**：`AddRadarPlugin` 只使用 payload 的显式 `_query` 键（不自动打包业务参数到 query string），各业务插件在拼完 URL 后 `exceptPayload('transaction_id')` 剔除业务参数（对齐 Stripe QueryPlugin 先例）。

### 3.5 回调验签（App Store Server Notifications V2）

**输入**：POST body `{"signedPayload": "<JWS>"}`（已验证：官方 responseBodyV2）。CallbackPlugin 骨架对齐 Wechat V3 CallbackPlugin（`init()` 取 `_request` → 验签 → `NoHttpRequestDirection` → payload 为解析结果）。

```
verifyAppleJws(signedPayload):
  1. 拆 JWS: header.payload.signature（base64url）
  2. header.alg == "ES256"；x5c 证书数组（DER base64）：叶子在首、Apple Root CA - G3 在尾
     （校验为「≥2 且末位与配置 root 一致」，不硬编码恰好 3 张——Apple 未来可能追加交叉签名证书）
  3. 链验证: x5c[0] → x5c[1] → ... → apple_root_ca；OID 检查:
     leaf 必须含 1.2.840.113635.100.6.11.1 / intermediate 必须含 1.2.840.113635.100.6.2.1
     （注意：与 token 链 OID 不同）
  4. openssl_verify(header.payload, signature, leafPubKey, SHA256)
  5. 解码 payload → responseBodyV2DecodedPayload
  6. 若 data.signedTransactionInfo / signedRenewalInfo 存在 → 递归二级验签解码
  7. 返回解析后数据（notificationType/subtype/notificationUUID/signedDate/data...）
```

验签失败抛 `InvalidSignException`（9500/9501）；`notificationUUID` 去重由业务层处理（SDK 原样返回）。

### 3.6 错误码（Exception.php 增量，对齐 WECHAT/ALIPAY 命名）

| 常量 | 值 | 语义 |
|---|---|---|
| `PARAMS_APPLE_URL_MISSING` | 9234 | URL 缺失/非法 |
| `PARAMS_APPLE_TOKEN_INVALID` | 9235 | token/回调结构非法 |
| `CONFIG_APPLE_INVALID` | 9413 | 配置非法 |
| `DECRYPT_APPLE_FAILED` | 9612 | 解密失败（含 RSA_v1 的 PHP 版本约束） |
| 复用 | 9500/9501 | `InvalidSignException`：SIGN_ERROR / SIGN_EMPTY |

### 3.7 注册链（已验证，Wechat/Allinpay 注册点逐一读过源码）

- `src/Pay.php`：`PROVIDER_APPLE` 常量 + `@method static Apple apple(...)` 注解 + `$providers[]` 追加 `AppleServiceProvider::class`
- `src/Config.php`：`PROVIDERS` 常量 + 构造函数 `match()` 分支 `new AppleConfig($config, $tenant)`
- `src/Service/AppleServiceProvider.php`：`makeService()` → `new Apple()`，`getProviderName()` → `Pay::PROVIDER_APPLE`
- composer.json **无需修改**（PSR-4 自动覆盖，且零新依赖）

---

## 4. 推进策略

```
阶段 1 — 契约 spike（可跳过）
├── 无真实环境：全部契约基于官方文档，标 0* 软依赖；不做真实 HTTP 抓包
└── 替代验证：本地自造密钥链（提交 tests/Cert/apple/）模拟 Apple 签名，运行时构造 token/JWS 验证代码路径

阶段 2 — 基础层（串行）：fixture 证书 + Certificate 内置证书 + 错误码 + AppleAction + AppleConfig
├── 验证点：配置类单测通过；phpstan/php-cs-fixer 通过

阶段 3 — Provider 骨架（串行，先于 Trait）：Provider + ServiceProvider + 注册链（提供 Apple::URL 常量）
└── 验证点：Pay::apple() 可实例化，多租户配置读取正确

阶段 4 — 密码学核心（串行）：AppleTrait（门面）+ Crypto/Apple/{Cryptor,TokenVerifier,JwsVerifier}（ASN.1/PKCS#7/ECDH/KDF/AES-GCM/JWS/JWT）+ fixture 构造器 + 单测
├── 验证点：自造 token 验签解密全链路单测通过；PKCS#7 与 openssl_pkcs7_verify 对拍
└── 里程碑：最大技术风险消化

阶段 5 — 业务插件（并行）：PayToken/Query×3/Refund/MerchantSession/Callback/AddRadar/Response + Shortcut×4
└── 验证点：各插件单测（Rocket 直装断言 + HTTP mock）

阶段 6 — 文档与收尾：web/docs 6 篇 + quick-start + sidebar + CHANGELOG + README
```

**回滚**：纯新增 Provider，无既有行为改动；回滚 = revert 对应 commit，不影响其他 Provider（注册链 3 处修改均为追加式）。

---

## 5. 风险与对策

| 风险 | 严重度 | 对策 |
|---|---|---|
| **无真实 Apple 环境实测**，验签/解密与真实 Apple 设备产物兼容性无法最终确认 | 高 | ① KDF/拼接/IV/tag 布局逐项与 etsy/PayU/samcorcos 三实现交叉核对（已做）；② fixture 证书提交 `tests/Cert/apple/` + 运行时自造 token/JWS 覆盖正反路径；③ 文档标注「未真实联调」+ 线上灰度建议；④ `apple_root_ca`/`apple_intermediate_ca` 可覆盖，证书轮换不阻塞 |
| PKCS#7 ASN.1 手动解析实现错误（context tag/SET OF/隐式标签/完整 DER 重构） | 高 | ① 纯函数方法 + 单测（自造 DER + 与 `openssl_pkcs7_verify` 对拍）；② signedAttrs 0xA0→0x31 有 PayU 源码先例 |
| **RSA_v1 需 PHP ≥ 8.5**（OAEP-SHA256 `digest_algo` 参数限制），8.2–8.4 无法解密 | 中 | 显式版本检查 + 明确异常（禁止静默降级 SHA-1）；文档/CHANGELOG 标注；EC_v1（主流）无版本要求 |
| fixture `openssl_pkcs7_sign` 输出为 S/MIME 格式（非裸 DER） | 中 | 构造器显式后处理：剥 MIME 头 → base64_decode 得 DER；测试断言 signature 字段可被本地 ASN.1 解析 |
| JWT DER→raw 转换错误导致 API 401 | 中 | 标准转换算法（含 33 字节前导零处理）+ 单测用 openssl 反向验签验证 |
| scope 蔓延（订阅状态机、退款编排等） | 中 | Must NOT：不做订阅业务状态机、不做退款发起（无此 API）、不做 consumption 上报、不做通知重试/去重持久化 |
| merchantSession 的 `_http` 证书口令进入 debug 日志 | 低 | 文档明确提示（Artful ignite 会记录 `rocket` 全量；使用方须在生产降低日志级别或避免在 payload 透传口令） |
| Apple API 域名/字段变更 | 低 | URL 常量集中定义；`_url`/`_service_url` 可覆盖（getRadarUrl 机制） |

---

## 6. 监控与可观测性

- 沿用仓库 Logger 惯例：每插件 `Logger::debug('[Apple][...] 插件开始装载', ['rocket' => $rocket])`；验签/解密失败自动带 error 上下文
- 事件：各操作触发 `MethodCalled`；callback 触发 `CallbackReceived`（骨架对齐 Wechat）
- 关键指标建议（文档侧提示，不新增代码）：验签失败率、回调 notificationType 分布、JWT 401 率

---

## 附录

### A. 配置示例

**A.1 最小配置**（仅验签解密）：

```php
'apple' => [
    'default' => [
        'merchant_id' => 'merchant.com.example',
        'payment_processing_cert' => '/path/to/payment-processing.pem',
        'mode' => Pay::MODE_NORMAL,
    ],
],
```

**A.2 完整配置**（+API 查询/退款/回调）：

```php
'apple' => [
    'default' => [
        'merchant_id' => 'merchant.com.example',
        'payment_processing_cert' => '/path/to/payment-processing.pem',
        'payment_processing_cert_passphrase' => null,
        'apple_root_ca' => null,               // 默认内置 src/Certificate/AppleRootCA-G3.pem
        'apple_intermediate_ca' => null,       // 默认内置 src/Certificate/AppleAAICAG3.pem（报文自带 intermediate 时不用）
        'issuer_id' => '69a6de87-0000-47e3-e053-5b8c7c11a4d1',
        'bundle_id' => 'com.example.app',
        'api_key_id' => 'X5D4K9J2Q1',
        'api_private_key' => '/path/to/SubscriptionKey.p8',
        'notify_url' => 'https://example.com/notify',
        'mode' => Pay::MODE_SANDBOX,
    ],
],
```

**A.3 回滚配置**：删除 `apple` 配置节点即完全移除该 Provider（注册链为追加式，不影响其他 Provider）。

### B. 官方文档索引（@see 注释用）

- Payment Token Format Reference：`developer.apple.com/documentation/passkit/payment-token-format-reference`
- Restoring the Symmetric Key：`developer.apple.com/documentation/passkit/restoring-the-symmetric-key`
- App Store Server API：`developer.apple.com/documentation/appstoreserverapi`（JWT 生成 / Get Refund History / Get Transaction Info / Get Transaction History / Get All Subscription Statuses）
- App Store Server Notifications V2：`developer.apple.com/documentation/appstoreservernotifications`
- Providing Merchant Validation：`developer.apple.com/documentation/applepayontheweb/providing-merchant-validation`
- Apple 证书：`apple.com/certificateauthority/`（AppleRootCA-G3 / AppleAAICAG3）
- 参考实现：`github.com/etsy/applepay-php`、`github.com/PayU-EMEA/apple-pay`、`github.com/samcorcos/apple-pay-decrypt`、`github.com/apple/app-store-server-library-python`

### C. 集成编排示例（Stripe 收单方全链路，业务方编排）

Apple Provider 与收单方 Provider 各司其职，由业务方编排（不做一体化耦合方法）：

```php
// ① 服务端创建订单 + 向收单方（Stripe）创建 PaymentIntent
$intent = Pay::stripe()->intent(['amount' => 1000, 'currency' => 'cny']);
$clientSecret = $intent->get('client_secret');

// ② 返回 client_secret + 金额给客户端 → 客户端拉起 Apple Pay 面板（PKPaymentRequest）
// ③ 用户授权后客户端回传 payment token

// ④ 服务端验签+解密 token（本 Provider 核心能力）
$card = Pay::apple()->payToken(['token' => $token]);
// $card: DPAN、transactionAmount、currencyCode、expirationDate...

// ⑤ 业务校验金额一致性 → 走收单方完成扣款（Stripe confirm 或收单方扣款 API）
// ⑥ 扣款结果返回客户端
```

注：若收单方 SDK 可直接消费 Apple token（如 Stripe iOS SDK 集成），步骤 ④ 可省略、由收单方代验——`payToken()` 适用于收单方要求服务端自验、或不经过收单方的业务（平台余额充值、积分、内部记账）。

### D. Apple Pay 完整支付流程说明

**角色分工**：Apple 只做 token 化（真实卡号 → DPAN）与凭证签发，**不碰资金流**；收单方（Stripe/银行）负责授权/清算/结算/退款；商户服务端负责验签解密 + 驱动收单方。

**App 内支付**：

```
① 服务端创建订单(金额/币种)
② 服务端向收单方创建 intent（如 Stripe PaymentIntent）——可选，视收单方集成方式
③ 返回订单+amount 给客户端
④ 客户端 PKPaymentRequest 拉起系统面板（无服务端参与）
⑤ 用户选卡+Face ID 授权
⑥ 系统生成加密 payment token（PKCS#7 签名 + AES-GCM 加密，密钥仅商户支付处理证书可解）
⑦ 客户端把 token 发给服务端
⑧ 服务端 Pay::apple()->payToken(['token' => $token]) 验签+解密 → DPAN/金额/币种
⑨ 服务端用卡数据向收单方完成扣款（Apple 不参与资金流）
⑩ 扣款结果返回客户端
```

**网页支付（Apple Pay on the Web）**：在 App 流程基础上，拉起面板前多一步 merchant session：前端 `onvalidatemerchant` 事件拿到 `validationURL`（单次有效、5 分钟过期）→ 发服务端 → 服务端用该 URL 请求 Apple 网关（TLS 双向认证，需商户身份证书）→ 返回 `merchantSession` 给前端 → 前端 `completeMerchantValidation` 继续拉起面板。其余步骤与 App 内一致。**SDK 侧证书透传方式**：`Pay::apple()->merchantSession(['validation_url' => $url, '_http' => ['cert' => [certPath, passphrase]]])`（Artful ignite 会将 `_http` 合并进 Guzzle Client options；⚠️ debug 日志会记录 payload，生产环境注意日志级别）。

**Apple Pay ≠ App Store IAP**：Apple Pay 用于实体商品/线下服务（资金走商户收单方）；App Store IAP 用于 App 内虚拟商品（Apple 收钱抽成后结算）。本 SDK 一期同时覆盖：`payToken()` 管 Apple Pay 场景；`query()/refund()/callback()` 管 IAP 场景。

---

**契约等级声明**：仓库结构/注册点/插件模式 = 已验证（源码完整读过）；Apple 协议（KDF、PKCS#7 结构、OID、JWS x5c、JWT claims、端点路径）= 已验证（Apple 官方文档原文 + 三个开源实现交叉核对）；「SDK 与真实 Apple 环境的端到端兼容」= 推断（未实测，无测试资源），风险见第 5 节。
