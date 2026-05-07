# yansongda/pay AGENTS.md

## OVERVIEW
PHP 支付 SDK，支持支付宝、微信、银联、抖音、江苏银行、PayPal、Stripe。基于插件管道架构，关注支付流程、回调验签和 Provider 扩展一致性。

## STRUCTURE
```
src/
├── Config/           # Typed Config（WechatConfig、AlipayConfig 等）
├── Event/            # PSR-14 事件
├── Plugin/{Provider}/V{n}/
├── Provider/         # Alipay、Wechat 等
├── Shortcut/{Provider}/
├── Traits/           # {Provider}Trait、ProviderConfigTrait
├── CertManager.php   # 证书缓存
└── Pay.php
```

## 核心组件
| 符号 | 位置 | 用途 |
|---|---|---|
| `Pay::alipay()` | `Pay.php` | 支付入口 |
| `Pay::wechat()` | `Pay.php` | 微信入口 |
| `ProviderInterface` | `Contract/` | 核心接口（pay/query/cancel/close/refund/callback/success） |
| `Rocket` | `Yansongda\Artful\Rocket` | 承载 params/payload/radar/destination |
| `CertManager` | `CertManager.php` | 证书读取与缓存 |
| `Event` | `Event.php` | PSR-14 事件分发 |
| `{Provider}Config` | `Config/` | 类型化配置对象 |

## 核心原则
- **安全第一**：回调/Webhook 必须验签，禁止直接信任传入数据
- **代码一致性**：新增 Provider 遵循现有结构

## COMMANDS & 开发规范
详见 `.agents/skills/dev-guide/SKILL.md`，使用时加载。

## 架构要点
- **插件管道**：`StartPlugin → [前置] → 业务插件 → [后置] → ParserPlugin`
- **动态调用**：Provider 通过 `__call` 调用 Shortcut，再进入管道
- **配置系统**：`Config` 类自动将数组配置转为 `{Provider}Config` 对象
- **事件系统**：`PayListener` 桥接 Artful 事件 → Pay 事件

## 签名验证（必须）
| Provider | 方式 | Trait 方法 |
|---|---|---|
| 支付宝 | RSA 本地验证 | `AlipayTrait::verifyAlipaySign()` |
| 微信 | 证书签名本地验证 | `WechatTrait::verifyWechatSign()` |
| PayPal | API 验证 | `PaypalTrait::verifyPaypalWebhookSign()` |
| Stripe | HMAC-SHA256 本地 | `StripeTrait::verifyStripeWebhookSign()` |
| 银联 | 证书签名本地验证 | `UnipayTrait::verifyUnipaySign()` |
| 江苏银行 | RSA 本地验证 | `JsbTrait::verifyJsbSign()` |
| 抖音 | SHA1 本地验证 | `CallbackPlugin::verifySign()` |

回调处理差异：
- **Wechat/Stripe/Paypal**：传 `_request`（ServerRequestInterface）到 CallbackPlugin
- **Alipay/Douyin/Unipay/Jsb**：通过 `getCallbackParams()` 获取 Collection

## Trait 参考
| 用途 | 方法（示例） |
|---|---|
| 配置获取 | `ProviderConfigTrait::getProviderConfig()`、`getTenant()`、`getRadarUrl()` |
| URL 构建 | `get{Provider}Url()`（各 Trait） |
| 签名验证 | `verify{Provider}Sign()`、`verify{Provider}WebhookSign()` |
| 证书管理 | `CertManager::getPublicCert()`、`getPrivateCert()` |
| 微信专用 | `decryptWechatResource()`、`reloadWechatPublicCerts()` |

> 完整方法列表见 `src/Traits/{Provider}Trait.php`

