# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v3.8.0-beta.6] - Unreleased

### Added

- 支付宝 OpenAPI V3 支持（RESTful `/v3/` 路径、JSON 报文、HTTP 头签名）：V3 管道整体就绪，`__call` **默认全部走 V2**（不做自动分流），需使用 V3 时通过 `Pay::alipay()->pay((new V3Shortcut)->getPlugins([]), $order)` 显式指定
  - V3 仅支持**证书模式**：与 V2 完全共用 `app_id`、`app_secret_cert`、`app_public_cert_path`、`alipay_public_cert_path` 配置，存量 V2 证书用户升级后调用 V3 接口零配置变更
  - 新增 V3 插件：`AddPayloadSignaturePlugin`、`AddRadarPlugin`、`VerifySignaturePlugin`、`ResponsePlugin` 及 `Pay/{Pos,Precreate,Query,Refund,Cancel,Close}Plugin`；`AlipayTrait` 新增 `getAlipayV3Url`/`getAlipayV3Authorization` 方法，验签统一复用 `verifyAlipaySign`
  - 异步通知（V2/V3 报文同构）由统一的 `Plugin\Alipay\CallbackPlugin` 自动完成 RSA2 验签（强制、不可关闭），应答为字面量 `success`
  - `Provider\Alipay` 新增 `V3_SANDBOX_URL` 常量（V3 沙箱网关与 V2 不同）

- 抖音支付全新接入「通用交易系统」（trade_basic），老的「担保支付」（ecpay）已全部删除：
  - `Pay::douyin()->mini($order)` 小程序 JSAPI 下单签名（`Plugin\Douyin\V1\Pay\InvokePlugin`）：透传官方 camelCase 下单参数（`outOrderNo`/`totalAmount`/`skuList`/`orderEntrySchema` 等），返回 `{data, byteAuthorization}`（SHA256-RSA2048 应用私钥签名），配合前端 `tt.requestOrder(data, byteAuthorization)` 完成下单，服务端不发 HTTP 请求
  - `Pay::douyin()->query($order)` 查询：`_action` 支持 `order`（默认，order_query）/`cps`（query_cps）/`refund`（refund_query）
  - `Pay::douyin()->refund($order)` 创建退款（refund_create，未传 `notify_url` 时自动注入配置 `notify_url`）；`['_action' => 'audit']` 退款审核（refund_audit_callback，`refund_audit_status` 1 同意/2 拒绝，拒绝时 `deny_message` 必填）
  - 统一回调入口：`Pay::douyin()->callback($request)` 统一处理 `payment` 支付结果/`refund` 退款结果/`pre_create_refund` 退款申请三类回调（`Plugin\Douyin\V1\CallbackPlugin`），均基于平台公钥 RSA 验签（`Byte-Timestamp`/`Byte-Nonce-Str`/`Byte-Signature` 三行验签串 + 原始 body），校验 body 顶层 `type` 非空后解析 `msg`，业务方按 `type` 分发处理；退款申请回调需业务方自行构造同步应答
  - `client_token` 自动获取与进程内缓存（`oauth/client_token`，`expires_in - 60` 秒提前过期），支持 `['_access_token' => ...]` 外部注入自建共享缓存
- 新增抖音配置字段：`app_id`（即 client_key）、`app_secret`、`app_private_key`（下单加签）、`douyin_public_key`（回调验签）、`notify_url`、`mode`
- 支付宝第三方应用授权（ISV 代理商模式）支持：新增 `auth` 快捷调用（`_action=token_app` 换取/刷新 `alipay.open.auth.token.app`、`_action=query` 查询 `alipay.open.auth.token.app.query`），插件归属 `Plugin/Alipay/V2/Open/Authorization`，补充 ISV 授权流程文档 (#1091)

- 支付宝应用网关验证（ISV）支持：`Pay::alipay()->callback($contents, ['_action' => 'gw'])` 处理开放平台「应用网关 URL」收到的请求（`_action` 也可放第一参数数组内），按「保留 `sign_type`、仅剔除 `sign`」的组串规则完成 RSA2 验签后解析 `biz_content`
  - `EventType=verifygw`（应用网关验证请求）：自动返回签名的 XML 应答（`Psr\Http\Message\ResponseInterface`，HTTP 200，`Content-Type: text/xml;charset=utf-8`，含应用公钥），业务侧直接 `return` 回吐即可，否则开放平台会报「网关地址和公钥验证失败」
  - 其他 `EventType` 网关消息：验签通过后透传 `Yansongda\Supports\Collection`，由业务侧自行处理并自行回吐官方要求的 ack XML（SDK 本期不做自动应答）
  - 验签失败不抛异常：返回 `success=false`、`error_code=VERIFY_FAILED` 的同结构 XML 应答；应答依赖公钥证书模式完整配置（`app_secret_cert`/`app_public_cert_path`），缺失时抛出 `InvalidConfigException`
  - 新增 `Yansongda\Pay\Plugin\Alipay\GatewayCallbackPlugin`；`ProviderInterface::callback()` 返回类型为 `Collection|ResponseInterface|Rocket`
  - 应答 `openssl_sign` 失败时抛出 `InvalidConfigException`（不再静默产出空签名）；webhook 形态下 `_action` 只能通过第二实参传入（详见文档）

- 支付宝 V2 响应内容 AES 解密支持（`alipay.user.info.share` 等敏感信息接口返回加密响应的场景）（#1204）
  - `AlipayConfig` 新增可选配置 `aes_key`（开放平台控制台「接口内容加密方式」生成的 base64 编码 16 字节 AES 密钥），不配置时明文响应行为零变化
  - `AlipayTrait` 新增 `decryptAlipayContents()` 静态方法（AES-128-CBC、16 字节全零 IV、密钥与密文双重 base64 解码），算法对齐官方 PHP/Java SDK
  - `VerifySignaturePlugin` 支持加密响应验签（签名源为带双引号的密文原文），验签通过后自动解密拆包交付明文；解密原语严格门控于验签之后（encrypt-then-MAC，未认证密文不可达）
  - `ResponsePlugin` 识别加密响应（`{method}_response` 为字符串）并以固定 `_cipher` 协议键交付（与 `_sign` 同构），密文无签名时沿用现有异常
  - 新增异常码 `DECRYPT_ALIPAY_AES_KEY_INVALID`（9610）/ `DECRYPT_ALIPAY_ENCRYPTED_DATA_INVALID`（9611），未配置密钥或密文非法时抛出带中文提示的异常

- 微信委托代扣「支付中签约」新增 `scan`（NATIVE 扫码，响应 `code_url`）、`h5`（MWEB，响应 `mweb_url`）、`mp`（公众号 JSAPI）支持；补全 `Wechat` Provider 的 `papay/pos/redpack` `@method` 注解，修复 IDE 无法提示 `papay` 方法的问题（#1111, #1113）

- 新增通联支付（Allinpay）Provider，对齐经典开放平台 apiweb（生产 `vsp.allinpay.com/apiweb` / 沙箱 `syb-test.allinpay.com/apiweb`）（#917, #1214）
  - 九个快捷方式：`unified`（统一支付，支持微信/支付宝/银联/数字人民币/云闪付等 paytype）、`scan`（被扫付款码）、`native`（主扫二维码）、`query`/`queryConfirm`（交易/确认查询）、`refund`（退款）、`cancel`（撤销）、`close`/`nativeClose`（关单/主扫关单）
  - RSA-SHA1 请求签名与响应/回调本地验签（对齐官方安全规范）；复合参数（`terminfo`/`extendparams`/`benefitdetail`）自动转为 json 字符串，保证签名与请求体一致
  - 配置：`cusid`、`appid`、`orgid`（可选）、`mch_secret_key`（商户私钥）、`allinpay_public_key`（通联公钥）、`notify_url`、`mode`（不支持服务商模式）；`version` 按接口区分默认值（pay/scanqrpay/refund/cancel=11，nativepay/query/queryconfirm/close/closenative=12），支持订单级覆盖
  - 新增异常码 `PARAMS_ALLINPAY_URL_MISSING`（9232）/ `CONFIG_ALLINPAY_INVALID`（9411）；全部插件带官方文档 `@see` 链接

### Changed

- **[BC]** 支付宝配置合并为单一 `Yansongda\Pay\Config\AlipayConfig`：删除 `AlipayV2Config`/`AlipayV3Config` 与 `version`、`alipay_public_key` 配置项（`version` 键不再生效）；配置必填为 `appId`/`appSecretCert`/`appPublicCertPath`/`alipayPublicCertPath`，`alipayRootCertPath` 改为 V2 管道调用时懒校验（V3 协议无 `root-cert-sn` 不需要）
- **[BC]** `ProviderConfigInterface` 从 `Yansongda\Pay\Config` 移动至 `Yansongda\Pay\Contract` 命名空间
- **[BC]** `CallbackReceived` 事件载荷统一为解析后的通知参数数组（原 V3 分支携带 `ServerRequestInterface`）；V3 同步验签的 `alipay-sn` 证书 SN 匹配校验为无条件执行
- 统一支付宝网关域名常量：`Provider\Alipay::URL` 仅保留纯域名（V2/V3 共用），V2 拼接完整请求 URL 时追加 `gateway.do?charset=utf-8`；移除 `V3_URL`，V3 沙箱经 `V3_SANDBOX_URL` 常量单独指向官方 V3 SDK 沙箱网关（`http://openapi.sandbox.dl.alipaydev.com`，与 V2 沙箱域名不同）

### Removed

- **BREAKING**: 删除抖音「担保支付」（ecpay）全部实现，升级用户需按官方指引迁移至「通用交易系统」，主要包括：
  - 删除插件：`Plugin\Douyin\V1\Pay\AddPayloadSignaturePlugin`、`Plugin\Douyin\V1\Pay\Mini\PayPlugin`、`Plugin\Douyin\V1\Pay\Mini\QueryPlugin`、`Plugin\Douyin\V1\Pay\Mini\RefundPlugin`、`Plugin\Douyin\V1\Pay\Mini\QueryRefundPlugin`（老 `AddRadarPlugin`/`ResponsePlugin`/`Pay\CallbackPlugin` 及三个 Shortcut 已按新交易系统重写，类名不变但实现与用法完全变化）
  - 删除配置字段：`mini_app_id`、`mch_id`、`mch_secret_token`、`mch_secret_salt`、`thirdparty_id`
  - 迁移要点：`mini_app_id` → `app_id`（即 client_key）；MD5 签名（`mch_secret_salt`）→ RSA 应用私钥加签（`app_private_key`，SHA256-RSA2048）；SHA1 回调校验（`mch_secret_token`）→ 平台公钥验签（`douyin_public_key`）；回调入参 form 数组 → 必须传 `ServerRequestInterface`（需 `Byte-*` 回调头验签），`callback()` 统一处理三类回调（按 body `type` 分发），保留宽签名但 array 入参不再支持回调处理（抛异常）
  - 老担保支付回调（form 参数 + SHA1 token 验签）不再兼容；存量担保支付订单的退款/查询请停留在 v3.7.x 或自行对接官方接口

### Fixed

- 修复支付宝 V2 管道遇到加密响应（`{method}_response` 为 base64 密文字符串）时 `array_merge` 触发 `TypeError` 崩溃的问题（#1204）

## [v3.8.0-beta.5] - 2026-09-05

### Added

- Stripe 支持通过 `_headers` 参数注入自定义请求头（如 `Idempotency-Key`、`Stripe-Version`、`Stripe-Account`），可覆盖默认值

### Changed

- 依赖要求变更为 `yansongda/artful ~1.2.0`、`yansongda/supports ~4.1.0`（#1192）
- 所有 Provider 配置统一校验 `mode` 合法性（`AbstractConfig::validate()` 阶段拦截），非法值由原来的 `Undefined array key` warning / 静默回退改为抛出 `InvalidConfigException(CONFIG_PROVIDER_INVALID)`；江苏银行不支持服务商模式（`MODE_SERVICE`），传入将被拒绝
- PayPal 回调验签前置校验扩展至全部 transmission/cert/algo 请求头，缺失时直接抛出异常，不再依赖 `verify-webhook-signature` API 返回失败才发现（#1196）
- PayPal 回调 body 非法 JSON 时抛出 `PARAMS_PAYPAL_BODY_INVALID` 异常，不再以 TypeError 崩溃（#1196）
- src 内 provider 名称 100% 常量化，统一引用 `Pay::PROVIDER_*` 常量（#1193）
- 消除 src 内剩余 2 处动态实例化 `new $var`，`Config` 的 Provider 配置类映射与 `AbstractServiceProvider` 改为静态实例化（#1194）
- `Config` 构造循环扁平化 + static-instantiation 设计文档入库（#1195）

### Fixed

- 修复 `WechatTrait::reloadWechatPublicCerts()` 在未指定 serial_no 时以 `null` 作数组偏移触发 PHP 8.5 deprecation 的问题（#1199）
- 修复 PayPal 请求 body 中残留业务参数的问题：查询/捕获/退款请求不再混入 `order_id`、`refund_id`、`capture_id` 等业务标识参数，GET 请求不再携带 body；web 支付顶层 `return_url`、`cancel_url`、`brand_name` 等参数不再与 `application_context` 内的值重复；全额退款时 body 为空，符合官方要求（#1196）
- 修复传入 `_return_rocket` 参数导致 PayPal access_token 缓存失效、每次调用重复获取 token 的问题（#1196）
- 修复 Stripe 查询/取消/退款查询请求残留内部参数的问题：`payment_intent_id`、`refund_id` 已拼入请求 URL，不再出现在 query string/body 中，避免被 Stripe 以 `parameter_unknown` 拒绝（此前查询、取消、退款查询接口实际不可用）
- Stripe PaymentIntent 支付（`intent`）缺 `amount`/`currency`、Checkout Session 支付（`web`）缺 `success_url` 时，改为在 SDK 侧直接抛出 `PARAMS_NECESSARY_PARAMS_MISSING` 异常，不再等 Stripe 返回 400 后才发现

## [v3.8.0-beta.4] - 2026-08-25

### Added

- 微信虚拟支付服务端 API 支持自动获取 access_token（配置 `virtual_pay.app_secret` 启用，stable_token 接口）（#1186、#1179）
- 微信虚拟支付客户端签名自动对 signData 做字典序排序，并在返回结果中提供与签名逐字节一致的 `signData` JSON 字符串（#1181）

### Changed

- 重构各 Provider Config 的必填参数校验：提取 `AbstractConfig::validateNotEmpty()` 公共方法，必填项仅需声明属性名列表（snake_case 键名由 `Str::snake()` 推导），除 Airwallex 外所有异常消息保持不变
- 统一 Airwallex 必填配置缺失时的异常消息格式，与其他 Provider 对齐：`配置错误: Airwallex 配置缺少 [client_id]` -> `配置异常: 缺少 Airwallex 配置 -- [client_id]`
- 各 Provider 配置校验时机从 `Pay::config()` 推迟到实际使用该 Provider 时；客户端签名返回字段顺序变为字典序（已按返回字段顺序集成的前端零改动）

### Fixed

- 修复仅使用单一 Provider 时其余已传入 Provider 的不完整配置导致 `Pay::config()` 抛出配置异常的问题（#1186）

### Removed

- 移除 `WechatConfig::validateForV2()/validateForMp()/validateForMini()` 方法（仅存在于 v3.8.0-beta 版本，SDK 内部无调用）


## [v3.8.0-beta.3] - 2026-08-11

### Changed

- 统一所有 Provider 的 _url payload 字段为前导 / 格式，base URL 常量去掉尾部 /

### Fixed

- 修复微信虚拟支付服务端 API 签名 uri 缺少前导 `/` 导致微信验签失败的问题（#1182）


## v3.8.0-beta.2 - 2026-07-01

### Added

- 新增微信小程序虚拟支付支持 (#1172)
  - 新增 `WechatConfigVirtualPay` 配置类（appKey、sandboxAppKey、offerId、encodingAesKey、callbackToken）
  - 新增虚拟支付插件：PayPlugin、CallbackPlugin、AddPayloadSignaturePlugin、VerifySignaturePlugin
  - 新增业务插件：Currency（代币）、Goods（商品）、Order（订单）、Subscribe（订阅）、Withdraw（提现）
  - 新增 `VirtualShortcut` 用于客户端签名场景
  - 新增 `WechatTrait::getWechatVirtualPaySignature()` 和 `getWechatVirtualSessionSignature()` 方法
  - `Wechat::success()` 支持 `['_action' => 'virtual']` 参数返回虚拟支付成功响应
  - 新增 `Wechat::URL_VIRTUAL` 常量（https://api.weixin.qq.com）

### Changed

- 移除所有 Provider 中未使用的 `mergeCommonPlugins` 方法 (#1173)

### Fixed

- 修复 `VirtualShortcut` 插件数组包含非 `PluginInterface` 实现的问题
- 修复虚拟支付测试用例缺少 `access_token` 参数的问题
- 移除测试文件中不必要的 `@internal` 和 `@coversNothing` 注解


## v3.8.0-beta.1 - 2026-05-12

### Fixed

- 微信回调增加时间戳验证防止重放攻击 (#1168)

### Changed

- 更新微信 V3 插件 @see 文档链接地址 (#1167)


## v3.8.0-beta.0 - 2026-05-08

### Added

- 增加 PHP 8.5 支持 (#1139)
- 新增 `Yansongda\Pay\Service\AbstractServiceProvider` 基类 (#1142)
- 新增 `Yansongda\Pay\CertManager` 类用于证书缓存管理 (#1142)
- 新增 Trait 系统替代 Functions.php (#1142, #1143, #1144)
  - `AlipayTrait` - 支付宝相关方法
  - `WechatTrait` - 微信相关方法
  - `UnipayTrait` - 银联相关方法
  - `DouyinTrait` - 抖音相关方法
  - `PaypalTrait` - PayPal 相关方法
  - `JsbTrait` - 江苏银行相关方法
  - `StripeTrait` - Stripe 相关方法
  - `ProviderConfigTrait` - Provider 配置方法
  - `SupportServiceProviderTrait` - ServiceProvider 支持方法
- 新增空中云汇 (Airwallex) 支付支持 (#1140)
- 新增 `AirwallexConfig` 类型化配置 (#1140, #1155)
- 新增 `Yansongda\Pay\Exception\NetworkException` 异常类 (#1157)
- 新增 EdgeCase 边界测试覆盖 (#1157)

### Changed

- 最低 PHP 版本要求从 8.0 升级到 8.2 (#1139)
- `Yansongda\Pay\Plugin\Wechat\V3\Marketing\MchTransfer\*` 重命名为 `Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\*` (#1139)
- 所有 Plugin 已迁移使用 Trait 方法代替 Functions.php 函数调用 (#1142, #1143, #1144)
- 所有 ServiceProvider 继承 `AbstractServiceProvider` 基类 (#1145)
- 所有 Provider 完成 typed config runtime migration (#1153, #1155)
- 证书逻辑集中到 `CertManager` (#1163)
- 规范化 `CertManager` 方法命名和错误码 (#1160)
- 代码质量优化 - PHPStan 清理、异常扩展、CertManager 重构 (#1157)
- 提取开发规范到 `dev-guide` Skill (#1164)
- 修正 `Config.php` 错误码误用 & `Pay::config()` 去重 (#1162)
- 修复 Trait 静态方法调用的 deprecation warnings (#1147)
- 更新 Scrutinizer 镜像以支持 PHP 8.2 (#1158)
- 升级 dev 依赖到 PHPUnit 11 / Mockery 1.6 / Monolog 3 / Symfony 6.4 并修复 PHPStan 错误
- 更新依赖到最新版本 (#1148)
- 新增 `pr-review-provider` skill 用于 Provider PR 代码审查 (#1149)

### Removed

- `src/Functions.php`，所有辅助函数已迁移到对应的 Trait (#1142, #1143, #1144, #1145)
- `tests/FunctionTest.php`，测试已迁移到 Trait 测试 (#1142, #1143, #1144, #1145)
- `src/Plugin/Wechat/StartPlugin.php`，请使用 `Yansongda\Artful\Plugin\StartPlugin` 代替 (#1139)
- `src/Plugin/Wechat/V3/Marketing/Transfer/` 目录（因微信支付 API 变更）(#1139)
- `get_alipay_config()`、`get_wechat_config()`、`get_unipay_config()` 函数，请使用 `get_provider_config()` 代替 (#1139)
- `WechatConfig` 中未使用的证书相关方法 (#1159)


## v3.7.20

### Added

- 新增 PayPal 支付 (#1127)
- 新增 Stripe 支付 (#1130)

### Fixed

- 修复 localhost 签名验证绕过漏洞 (GHSA-q938-ghwv-8gvc) (#1131)


## v3.7.19

### Added

- 增加支付宝商品文件上传插件 (#1120)


## v3.7.18

### Fixed

- 微信分账参数可能丢失的问题 (#1108)


## v3.7.17

### Fixed

- 事件缺失与不生效的问题 (#1106)


## v3.7.16

### Added

- 新增微信商户转账查询接口 Shortcut (#1099)
- 微信商家转账支持内置异步通知参数（#1100）


## v3.7.15

### Added

- 新增最新版微信商户转账撤销接口（#1096）


## v3.7.14

### Added

- 优化私钥证书的字符串读取方式（#1081）


## v3.7.13

### Added

- 新增支付宝APP同步回调验签 (#1061, #1064)


## v3.7.12

### Added

- 支持最新版微信商户转账 (#1058)


## v3.7.11

### Added

- 新增微信分账申请分账账单插件 (#1041)


## v3.7.10

### Fixed

- 未配置微信证书时，自动获取证书后仍然使用之前的微信配置(#1026)


## v3.7.9

### Added

- 新增抖音支付(#1014)


## v3.7.8

### Added

- 新增 v3 付款码服务商模式(#1010)


## v3.7.7

### Added

- 新增江苏银行e融支付(#1002)


## v3.7.6

### Fixed

- 微信关闭订单报解包错误的问题(#1000, #1001)


## v3.7.5

### Changed

- 优化微信 `ResponsePlugin` 插件去除不必要的返回参数(#996)

### Deprecated

- 微信 `StartPlugin` 改为使用 `yansongda/artful` 中的插件(#993)
- `get_wechat_config`, `get_alipay_config`, `get_unipay_config` 方法已废弃，使用 `get_provider_config` 方法代替(#994)

### Fixed

- 支付宝响应空签名时签名验证逻辑错误的问题(#998)


## v3.7.4

### Changed

- 使用 is_file 代替字符串结尾判断(#982)


## v3.7.3

### Fixed

- 修复商家转账参数缺失的问题(#977)


## v3.7.2

### Added

- 微信V2版本支持普通红包(#973)

### Changed

- 升级 `yansongda/artful` 到最新版解决 http 配置不生效的问题(#974)


## v3.7.1

### Fixed

- 修复微信付款码 shortcut 支付插件执行顺序错误(#972)


## v3.7.0

### Added

- 支持微信 v3 版付款码支付(#969)

### Changed

- 微信付款码支付更改为 v3 版(#969)


## v3.6.5

### Added

- 支付宝根证书配置支持直接配置内容(#959)


## v3.6.4

### Fixed

- 修复支付宝授权访问令牌插件参数问题(#954)


## v3.6.3

### Changed

- 优化微信错误响应时的处理逻辑(#944)


## v3.6.2

### Fixed

- 修复微信 App 支付参数异常问题(#941)


## v3.6.1

### Changed

- 升级 `yansongda/artful` 到 v1.0.9 修复 JsonPacker 为空时 packer 错误的问题(#937)


## v3.6.0

### Added

- 新增 `InvalidSignException`(#903)
- 新增 `DecryptException`(#906)
- 新增 `decrypt_wechat_contents` 解密微信加密内容(#912)
- `\Yansongda\Pay\Plugin\Wechat\Extend\Complaints\QueryDetailPlugin` 自动解密用户手机号(#912)
- 支持 微信/支付宝 多版本(#918)
- 增加 `HttpClientFactoryInterface` 方法用于工厂模式创建 http client(#921)
- 增加银联 `条码支付综合前置平台-被扫支付` 刷卡支付插件(#922)
- 增加小程序虚拟支付签名、用户签名方法(#924)
- 增加微信发票插件(#927)

### Changed

- 查询API方法由 `find` 改为 `query`，同时参数只支持 array(#897)
- cancel/close 的 API 参数只支持 array，不再支持 string(#900, #901)
- 微信合单支付去掉独立的 `combine_app_id`,`combine_mch_id` 配置，复用其它配置(#909)
- 手机网站支付快捷方式由 wap 改为 h5(#911, #915, #916, #934)
- `Pay` 类对外方法由所改变，如果您有自行扩展相关插件，请检查(#926)
- change(internal): 按场景对 支付宝/微信/银联 插件进行分类 && 插件代码优化(#894, #909, #913, #922)
- change(internal): 将 支付/微信/银联 shortcut 从 plugin 文件夹独立出来(#895, #904, #905, #933)
- change(internal): shortcut 完整标明各个插件，不使用 commonPlugin(#886)
- change(internal): DirectionInterface 方法由 `parse` 改为 `guide`(#896)
- change(internal): 错误代码 const 命名规则统一(#902, #903, #906, #909, #926)
- change(internal): 调整 `ProviderInterface` 的返回参数，增加了 `Rocket` 返回(#909)
- change(internal): 将 `call()` 方法重命名为 `shortcut()`(#914)
- change(internal): `mergeCommonPlugins` 不再作为 `AbstractProvider` 的方法(#918)
- change(internal): `AbstractProvider` 默认使用 `HttpClientFactoryInterface` 创建 http client(#921)
- change(internal): 调整 银联 插件文件夹结构(#923)
- change(internal): 替换为 `artful` API 请求框架(#926)
- change(internal): 调整微信代金券插件文件结构(#928)


## v3.5.3

### Changed

- 增加支付宝 分账关系维护/分账查询 插件(#874)
- 支付宝公钥使用公共函数获取(#835)


## v3.5.2

### Fixed

- monolog 不存在时报错问题(#834)
- `\Yansongda\Pay\Provider\AbstractProvider::call` 方法返回值类型错误问题(#834)


## v3.5.1

### Fixed

- `destination` 的类型约束去掉 array(#824)


## v3.5.0

### Removed

- 移除 `Yansongda\Pay\Direction\ArrayDirection` 类(#818, #819)


## v3.4.2

### Changed

- 只支持 hyperf3.x 版本(#815)


## v3.4.1

### Changed

- 优化无签名时错误提示(#813)
- 优化预下单失败时错误提示(#814)


## v3.4.0

### Added

- 增加 `get_direction` 方法获取 `Direction` 对象(#803)

### Changed

- `Exception::INVALID_PARSE` 更改为 `Exception::INVALID_DIRECTION`(#804)
- 最低支持版本变更为 php8.0(#801)
- 优化 coding style 代码规范(#802)


## v3.3.1

### Fixed

- 支付宝沙箱地址(#800)


## v3.3.0

### Added

- 支持微信 v2 版本刷卡支付(#753)
- 增加申请代扣协议插件(#767)
- 增加支付中签约插件(#763)
- 增加只签约插件(#765)
- `shortcut` 支持 `_no_common_plugins` 参数不使用通用插件(#771)
- 增加委托代扣 shortcut(#773)

### Changed

- 重构 ArrayParser 类(#754)
- coding style(#769)
- 优化现有微信v2插件代码(#772)
- 所有参数判断使用 `$payload->has()` 判断是否存在(#778)
- 支持 psr/http-message 2.0 版(#784)
- 所有的 `Find*Plugin` 调整为 `Query*Plugin`(#756)
- 插件开始装载日志由 `info` 调整为 `debug`(#755)
- ParserInterface 签名由 `?ResponseInterface $response` 变更为 `PackerInterface $packer, ?ResponseInterface $response`(#754)
- \Yansongda\Pay\Plugin\Wechat\RadarSignPlugin 增加 `__construct(JsonPacker $jsonPacker, XmlPacker $xmlPacker)` 方法(#753)
- 所有 `Parser` 更名为 `Direction`(#770, #774)
- '_type' 类型统一定义为渠道id，如: 小程序id，公众号id等；增加 '_action' 为操作类型用于 shortcut(#781)
- 默认 container 由 `php-di/php-di` 改为 `hyperf/pimple`(#786)

### Removed

- 移除废弃的类(#752)

### Fixed

- 微信代金券 api 参数错误问题(#777)


## v3.2.14

### Fixed

- 微信投诉相关插件响应解析错误(#746)


## v3.2.13

### Changed

- 微信退款可取消 notify_url(#741)


## v3.2.12

### Added

- 增加获取微信平台公钥证书方法(#733)


## v3.2.11

### Changed

- 增加微信转账注释方便ide识别(#725)


## v3.2.10

### Fixed

- CallbackReceived 事件在获取到回调参数后触发(#716)


## v3.2.9

### Fixed

- 当配置文件出错微信解密失败后报错的问题(#698)


## v3.2.8

### Fixed

- 商家批次单号查询批次单时 query 参数不正确(#690)


## v3.2.7

### Fixed

- 微信批次单号查询批次单时 query 参数不正确(#688)


## v3.2.6

### Fixed

- json 中有 `&` 时解析错误(#687)


## v3.2.5

### Fixed

- 修复支付宝 subject 中存在 + 号回调验签不通过(#684)


## v3.2.4

### Added

- 银联支付(#662)


## v3.2.3

### Added

- 微信 Native 支付支持关联其它类型 appid(#680)


## v3.2.2

### Changed

- 优化支付宝 launch 插件代码(#678)

### Deprecated

- deprecated: 支付宝 `RadarPlugin`, `SignPlugin` 已废弃，使用 `RadarSignPlugin` 代替(#678)
- deprecated: 微信 `SignPlugin` 已废弃，使用 `RadarSignPlugin` 代替(#678)


## v3.2.1

### Changed

- 优化 `wechat_public_cert_path` 配置(#674)

### Fixed

- `wechat_public_cert_path` 未配置时报错的问题(#674)


## v3.2.0

### Changed

- Function 增加命名空间(#665)
- `get_alipay_config`，`get_wechat_config` 返回类型由 `Config` 改为 `array`(#667)
- 支付宝转账查询接口由老版本改为为新版本(#666)
- 支付宝中支付宝根证书、应用证书序列号在常驻进程中缓存(#668)

### Removed

- Function 中将 `get_wechat_authorization` 方法移除(#664)


## v3.1.12

### Changed

- 优化代码 (#661)

### Fixed

- 微信代金券详情 url 不正确(#663)


## v3.1.11

### Added

- 微信退款自动增加回调url(#649)


## v3.1.10

### Added

- 支付宝周期扣款签约接口(#644)


## v3.1.9

### Fixed

- 微信服务商模式预下单存在子商户appid时，invoke 时也应该为子商户 appid (#638)


## v3.1.8

### Fixed

- 提前读取响应数据造成数据错误的问题(#633, #634)


## v3.1.7

### Fixed

- 微信内网页支付供应商模式 sub_appid 非必填(#628)


## v3.1.6

### Fixed

- 微信注释中返回类型错误(#630)


## v3.1.5

### Added

- 微信服务商退款及查询退款支持自动 sub_mchid 参数(#619)


## v3.1.4

### Added

- 支持微信投诉API (#614)


## v3.1.3

### Added

- 配置文件增加第三方应用授权token的支持 (#602)


## v3.1.2

### Fixed

- alipay 中 event dispatch provider 是 wechat 的问题 #595


## v3.1.1

### Fixed

- 设置 container，强制更新 config 后 container 不是设置的 container 的问题 #591


## v3.1.0

### Changed

- 移除 `php-di/php-di` 依赖。如果您使用的框架非 `hyperf`, `laravel` 或 没有指定 `ContainerInterface`，仍需手动安装 `composer require php-di/php-di`
- 移除 `guzzlehttp/guzzle` 依赖。如果没有指定 `\Yansongda\Pay\Contract\HttpClientInterface` 仍需手动安装 `composer require guzzlehttp/guzzle`
- 升级 `yansongda/supports` 到 `~v3.2.0`
- 升级 `php` 最低版本到 `7.4.0`
- 自动识别 `hyperf`, `laravel` 框架，使用相应的 `container` 减少内存占用
- 完全支持 `psr11`，可手动传入 `ContainerInterface` 使用
- `Pay::config(array $config = [], $container = null)` 方法第二个参数增加为 $container，可手动传入 `ContainerInterface`/`Closure`。注意 `Closure` 需最终返回一个 `ContainerInterface` 的实例。

### Fixed

- 解决 php8.1 下 deprecated 的提示


## v3.0.27

### Fixed

- 添加分账接受人姓名加密字段错误 (#566)


## v3.0.26

### Added

- 支持 psr/log 2.x and 3.x (#562)


## v3.0.25

### Fixed

- 支持分账传递姓名 (#559)


## v3.0.24

### Added

- 支持使用小程序等其他类型转账 (#552)


## v3.0.23

### Fixed

- 未设置微信公钥证书时，加密不生效的问题 (#549)


## v3.0.22

### Fixed

- 微信分账传递姓名时未加密的问题 (#547)


## v3.0.21

### Added

- 微信转账快捷方式与加密方式支持 (#542)


## v3.0.20

### Changed

- 完善支付宝响应错误时的异常信息 (#530)


## v3.0.19

### Fixed

- 支付宝 system.oauth.token 请求参数错误 (#528)


## v3.0.18

### Added

- 电商收付通的退款使用 _type 增加多类型 appid (#518)


## v3.0.17

### Added

- 增加电商收付通的退款相关插件 (#513)


## v3.0.16

### Fixed

- app 支付调起签名问题 (#1389476)


## v3.0.15

### Fixed

- 下载对账单时响应解析 (#df27f95)


## v3.0.14

### Fixed

- app 支付调起签名中参数大小写问题 (#7916fdd)


## v3.0.13

### Fixed

- app 支付调起签名中时间戳参数大小写问题 (#510)


## v3.0.12

### Fixed

- 微信小程序支付供应商模式 sub_appid 非必填 (#509)


## v3.0.11

### Added

- 微信 h5 支付支持关联 mini_app_id (#506)


## v3.0.10

### Added

- 服务商批量转账到零钱 (#503)


## v3.0.9

### Added

- 支持直连商户批量转账到零钱 (#501)


## v3.0.8

### Fixed

- 设置 bcscale 时支付宝根证书计算错误的问题 (#492, #494)


## v3.0.7

### Fixed

- 支付宝 wap/web 支付 get 方法时url拼接问题 (#488)


## v3.0.6

### Changed

- 优化服务商模式小程序下单场景 (#487)


## v3.0.5

### Fixed

- 服务商模式交易查询 (#483)


## v3.0.4

### Added

- 支持服务商模式 (#479)
- 支持微信服务商分账功能 (#480)


## v3.0.3

### Added

- 公钥证书增加 cer 后缀支持 (#d22e29a)


## v3.0.2

### Fixed

- 修复微信支付关闭订单时报错问题 (#475)


## v3.0.1

### Fixed

- 修复微信支付关闭订单时报错问题 (#475)
