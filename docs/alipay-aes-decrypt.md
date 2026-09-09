# 技术设计：支付宝 AES 内容解密支持

> **时间**：2026-09-07
> **作者**：GLM + yansongda
> **状态**：已实施

## 1. 背景与问题

**现状**：支付宝开放平台存在「接口内容加密」体系（敏感信息如会员手机号，接口返回的 `{method}_response` 节点是 AES 密文字符串而非 JSON object）。本 SDK 支付宝侧（V2 管道）完全没有加解密代码；`AlipayConfig` 无 AES 密钥字段。官方流程：**先对密文验签、后解密**（AES-128-CBC + 全零 IV + PKCS7）。

**困境**：

1. **加密响应会让现有管道直接崩溃**：`V2/ResponsePlugin` 拆包时 `array_merge(['_sign' => ...], $response)`，当 `{method}_response` 为密文字符串时 PHP 8.2 抛 TypeError（已验证，读过源码）。
2. **验签源不匹配**：`V2/VerifySignaturePlugin` 用 `json_encode($result, JSON_UNESCAPED_UNICODE)` 组验签串；而官方对加密响应的签名源是**带双引号的密文原文** `"密文"`（依据官方 PHP/Java SDK 源码，已验证；真机未实测）。
3. **无解密能力**：即使不崩溃，用户拿到的是密文，无法使用。
4. 本 SDK FAQ 历史上明确「只支持 RSA2」——该结论需随官方能力演进打破。

**目标**：**配置 aesKey 后开箱即用自动解密**；**不配置 aesKey 的现有用户零行为变化**；**常规（明文）响应路径零性能与逻辑变化**；**算法严格对齐官方规范**。

## 2. 整体方案

**核心思路**：沿用微信既有三层模式（Config 字段 + Trait 静态解密方法），在 V2 响应链 `VerifySignaturePlugin` 验签通过之后执行解密（解密原语严格门控于 `verifyAlipaySign()` 正常返回之后，未认证密文不可达，满足官方「先验签后解密」顺序与 encrypt-then-MAC 原则）。`ResponsePlugin` 拆包时通过「`{method}_response` 值是否为 string」识别密文，并以固定 `_cipher` 协议键（与 `_sign` 同构）交付给下游。

```
                        V2 响应链（after 阶段逆序）
┌─────────────────────────────────────────────────────────────────────┐
│ ParserPlugin(解析) → ResponsePlugin(拆包，密文以 `_cipher` 协议键交付)│
│         → VerifySignaturePlugin(验签: 密文用带引号密文源；            │
│            验签通过后密文场景 → AES解密 → JSON → Collection 拆包交付)  │
│         → ... 其余插件                                                │
└─────────────────────────────────────────────────────────────────────┘
```

**涉及文件**：

```
src/
├── Config/AlipayConfig.php                    # aesKey 字段
├── Traits/AlipayTrait.php                     # decryptAlipayContents()
├── Exception/Exception.php                    # 2 个 DECRYPT 异常码
└── Plugin/Alipay/V2/
    ├── ResponsePlugin.php                     # 密文识别与拆包兼容
    └── VerifySignaturePlugin.php              # 密文验签源 + 验签后解密拆包
tests/...(镜像)
web/docs/v3/...                                  # aesKey 配置说明 + FAQ
```

## 3. 详细设计

### 3.1 配置设计

`AlipayConfig` 新增可选字段（snake_case `aes_key` 自动映射 setter）：

| 字段 | 类型 | 必填 | 说明 |
|---|---|---|---|
| `aesKey` | `?string` | 否 | 开放平台控制台「接口内容加密方式」生成的 **base64 AES 密钥**；懒校验，不进 `validateRequired()` |

> 注：官方控制台生成的密钥预期为 16 字节（AES-128），但该口径**未实测**（官方 PHP/Java SDK 源码均不强制长度，Java 按密钥长度自动选择 AES-128/192/256）。本方案按已确认决策**严格 16 字节**：非 16 字节密钥抛配置异常并明确提示；若真实环境出现非 16 字节密钥，属设计性偏差，停下报告再定扩展策略。

配置示例：

```json
{
  "alipay": {
    "default": {
      "app_id": "2021000000000000",
      "app_secret_cert": "...",
      "app_public_cert_path": "...",
      "alipay_public_cert_path": "...",
      "alipay_root_cert_path": "...",
      "aes_key": "JcH8A+12EXAMPLE+KeyB64=="
    }
  }
}
```

### 3.2 算法设计（Trait）

`AlipayTrait` 新增静态方法（与微信 `decryptWechatResource` 同模式）：

```php
decryptAlipayContents(string $contents, AlipayConfig $config): string
```

伪代码：

```
aesKey = config.getAesKey(); 为空 → InvalidConfigException(KEY_INVALID)
key = base64_decode(aesKey, strict); 失败或长度≠16 → InvalidConfigException(KEY_INVALID)
data = base64_decode(contents, strict); 失败 → DecryptException(DATA_INVALID)
plain = openssl_decrypt(data, 'aes-128-cbc', key, OPENSSL_RAW_DATA, 16字节全零IV)
plain === false → DecryptException(DATA_INVALID)
return plain   // 明文 JSON 字符串；json_decode 留给调用方，保持方法纯粹
```

严格 16 字节（AES-128）为**已确认的方案决策**；注意「官方密钥为 16 字节」属推断（官方 SDK 源码不强制长度，见 3.1 注），故解密遇到非 16 字节密钥时抛明确异常引导，而非猜测行为。

**密文判定的已知边界**：`ResponsePlugin` 以「resultKey（`{method}_response`）的值是否为 string」精确匹配判定密文。理论上若某明文接口的 `{method}_response` 恰为单键字符串值，将被误判为密文：验签源从对象形态变为带引号字符串，导致合法响应抛 `InvalidSignException`（显式失败，不会静默出错）。经核实 SDK 内 8 个 Shortcut 涉及的全部接口响应节点均为 JSON object（含 `code`/`msg` 等多键），实际风险趋近于零。另，`_cipher` 为 SDK 内部保留协议键（`_` 前缀，与 `_sign` 同构），明文业务数据理论上若含同名键会被 `VerifySignaturePlugin` 误判为密文；支付宝业务字段不以 `_` 开头，风险同样趋近于零。

### 3.3 插件设计

**(a) `ResponsePlugin`**：拆包时识别 `$response` 为 string（密文）→ 走独立分支 `destination = ['_sign' => $sign, '_cipher' => $密文]`（不再 array_merge string；密文以固定 `_cipher` 协议键交付，与 `_sign` 同构，跨插件契约为单一协议常量）；sign 为空时抛 InvalidResponseException（加密响应必然带签名，sign 空说明网关异常）。明文路径完全不变。

**(b) `VerifySignaturePlugin`**：验签前检查 destination 中的 `_cipher` 协议键（由 `ResponsePlugin` 拆包时写入）——当其值为 string 时（密文形态），签名源改为 `json_encode($密文, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)`（即 `"密文"`，`/` 不转义与官方原文一致；base64 字符集无其他需转义字符）；否则走现有逻辑。**验签通过后**，密文场景在此解密：`decryptAlipayContents()` → `json_decode(assoc)` → 解码非数组抛 DecryptException → `setDestination(['_sign' => ...] + 明文数组)`（`_cipher` 自然丢弃）。解密原语严格门控于 `verifyAlipaySign()` 正常返回之后（encrypt-then-MAC：未认证密文在控制流上不可达解密，结构性消除 padding oracle 面）。未配置 aes_key 时验签通过后抛 InvalidConfigException（9610）。明文响应路径零变化（含未配置 aes_key 场景）。

### 3.4 接口对接契约（关键标注）

| 契约 | 状态 |
|---|---|
| 算法：AES-128-CBC、全零 IV、PKCS7、base64 密文 | **已验证**（官方文档 + 官方 PHP/Java SDK 源码三方一致）；密钥长度 16 字节为**推断**（官方 SDK 不强制，本方案严格 16 字节为已确认决策） |
| 加密响应形态：`{"xxx_response":"base64密文","sign":"..."}`，密文为字符串 | **已验证**（官方文档 my.getPhoneNumber 场景 + 官方 SDK `isDataEncrypted = !content.startsWith("{")`） |
| 加密响应验签源 = 带双引号密文原文 | **已验证（官方 SDK 源码）/ 真机未实测** |
| 适用接口：`alipay.user.info.share`、小程序手机号、芝麻、内容安全等 | 已验证（官方文档）；具体某接口整体加密行为按官方通用机制推断 |
| 异步通知（CallbackPlugin）不加密 | 推断（官方文档未见通知加密场景）；Trait 方法可复用于自定义验签后解密 |
| ISV 场景：商家应用 aesKey 作为独立租户配置传入 | 已验证（官方 `alipay.open.auth.app.aes.get` 机制 + 本 SDK 租户机制） |

请求/响应示例（`alipay.user.info.share`）：

```json
// HTTP 原始响应（加密时）
{"alipay_user_info_share_response":"ts6oE...base64...==","sign":"gX2...","sign_type":"RSA2"}
// 解密后的 destination（最终交付形态）
{"_sign":"gX2...","code":"10000","msg":"Success","mobile":"138****8000","nick_name":"..."}
```

### 3.5 兼容性设计

- 不配 `aesKey`：明文响应零变化；密文响应在验签通过后抛出**明确**的「缺少 AES 密钥」异常。
- 配 `aesKey`：明文接口行为不变；加密接口自动解密。
- V3 管道不涉及（V3 无 AES 内容加密体系）；`CallbackPlugin`/`AppCallbackPlugin` 不改动。
- **请求侧加密**（`encrypt_type=AES` + 整体加密 biz_content 上送）**不在本期范围**，仅 ISV 密钥管理类接口（`alipay.open.auth.app.aes.set/get`）需要，列为后续可选扩展。

## 4. 风险与对策

| 风险 | 严重度 | 对策 |
|---|---|---|
| 官方加密响应验签源格式与推断不符（真机未实测） | 中 | 真实环境实测；若不符按用户提供的原始报文修正组串逻辑（隔离在 Verify 插件单点） |
| 控制台重新生成 aesKey 后旧密钥立即失效 | 低 | 文档明示；解密失败抛 DecryptException 带中文提示引导检查密钥 |
| 支付宝个别接口返回非整段加密（字段级）或 AES_V2（随机 IV）变体 | 中 | 密文判定不解（值为 string）时按 v1 算法尝试；失败异常中输出 debug 信息；留后续扩展点 |
| 用户误配非 16 字节 key / 官方密钥口径与 16 字节不符 | 低 | 严格校验 + 明确异常文案；若真实环境出现官方下发的非 16 字节密钥，属设计性偏差，停下报告后再议宽容分支（官方 Java SDK 按长度自适应 128/192/256） |
| 明文响应的 `{method}_response` 恰为字符串值被误判为密文（见 §3.2） | 低 | 仅影响含验签插件的 V2 管道；SDK 范围内所有接口响应节点均为多键 object（含 code/msg）；失败模式为显式 `InvalidSignException` 而非静默错误 |
| 明文业务数据含 `_cipher` 保留键被误判（见 §3.2） | 低 | `_` 前缀为 SDK 内部保留命名空间（`_sign` 先例）；支付宝业务字段不以 `_` 开头；失败模式同为显式 `InvalidSignException` |
| 加密 `error_response`（官方 SDK 对错误响应也有解密防御） | 低 | 本方案中加密错误响应走 fallback 多键路径 → 验签失败抛 `InvalidSignException`，与现状行为一致（无回归）；留扩展点，遇真实场景再补 |
| 现有明文路径回归 | 低 | 现有全量测试守护 + 明文分支零改动原则 |

## 5. 监控与可观测性

SDK 无上报通道，沿用现有 `Logger`：解密分支在验签通过后 `Logger::info`、失败抛异常（含中文提示）。用户侧观察点：日志出现 `[Alipay][VerifySignaturePlugin] 响应解密成功`；对接异常监控需**分别捕获两类异常**——`InvalidConfigException`（9610，密钥缺失/格式错误）与 `DecryptException`（9611，密文非法/解密失败），两者无继承关系，只捕获一类会漏报；两者均仅在验签通过后抛出（密钥/密文问题），验签失败始终抛 `InvalidSignException`。
