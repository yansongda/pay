---
name: pr-review-provider
description: "Use when reviewing PRs that add or modify a payment Provider in yansongda/pay - covers plugin pipeline, multi-tenant safety, signature verification, docs, and naming conventions."
---

# PR Review Checklist — Payment Provider

yansongda/pay 新增/修改 Provider 时的 Code Review 专用检查清单。基于 Airwallex PR #1140 review 经验沉淀。

## Review 流程

按以下阶段顺序审查，确保覆盖完整：

1. **Phase 1**: Provider 结构完整性
2. **Phase 2**: 代码规范检查
3. **Phase 3**: 安全性检查（签名验证、加密解密）
4. **Phase 4**: 架构一致性（管道、Trait、Event）
5. **Phase 5**: 官方文档对照
6. **Phase 6**: 测试覆盖
7. **Phase 7**: 文档完整性

---

## Phase 1: Provider 结构完整性

对照以下清单逐项检查：

| # | 检查项 | 位置 | 说明 |
|---|--------|------|------|
| 1 | 插件 | `src/Plugin/{Provider}/V{n}/` | 按版本组织 |
| 2 | Provider 类 | `src/Provider/{Provider}.php` | 实现 `ProviderInterface` |
| 3 | 服务提供者 | `src/Service/{Provider}ServiceProvider.php` | 服务注册 |
| 4 | 快捷方式 | `src/Shortcut/{Provider}/` | `{Method}Shortcut.php` |
| 5 | Trait 方法 | `src/Traits/{Provider}Trait.php` | `get{Provider}Url`、`verify{Provider}WebhookSign` 等 |
| 6 | Provider 注册 | `src/Pay.php` | 添加 `{Provider}::class` 和入口方法 |
| 7 | 异常常量 | `src/Exception/Exception.php` | `PARAMS_{PROVIDER}_*`、`CONFIG_{PROVIDER}_*` |
| 8 | 测试 | `tests/` | 与源码结构对应 |
| 9 | 文档 | `web/docs/v3/{provider}/` | VitePress 文档 |
| 10 | 侧边栏/CHANGELOG | `web/.vitepress/sidebar/v3.js`、`CHANGELOG.md` | 更新配置 |

**注意**：`src/Functions.php` 已不存在，URL/签名等方法已下沉到 `src/Traits/*Trait.php`。

---

## Phase 2: 代码规范检查

### 基本规范

| 检查项 | 要求 |
|--------|------|
| `declare(strict_types=1)` | 每个文件必须有 |
| `use` 导入 | 禁止直接写完整命名空间（如 `\Yansongda\Pay\...`） |
| 多行条件 | `&&` / `||` 放在续行开头 |

### 命名规范

| 类型 | 格式 | 示例 |
|------|------|------|
| 插件 | `{Action}Plugin.php` | `PayPlugin`、`RefundPlugin` |
| 快捷方式 | `{Method}Shortcut.php` | `WebShortcut`、`QueryShortcut` |
| Provider | `{ProviderName}.php` | `Paypal.php`、`Stripe.php` |
| ServiceProvider | `{ProviderName}ServiceProvider.php` | `PaypalServiceProvider.php` |
| Trait | `{Provider}Trait.php` | `WechatTrait`、`StripeTrait` |
| 命名空间 | `Yansongda\Pay\Plugin\{Provider}\V{n}\Pay\{Plugin}` | 版本号与 API 版本一致 |

### 日志与异常

- 日志格式：`[Provider][V{n}][Category][Plugin]`，使用中文消息
- 异常常量：`PARAMS_{PROVIDER}_*`、`CONFIG_{PROVIDER}_*`
- 异常消息：中文，附带上下文参数

---

## Phase 3: 安全性检查

### 1. params vs payload 混淆

**高危**：`Artful::artful()` 第二参数必须是 `params`（含 `_config`），不能是 `payload`。

```php
// ❌ 错误 — payload 不含 _config，多租户必崩
$result = Artful::artful([...], $confirmPayload);

// ✅ 正确 — params 含 _config 租户标识
$result = Artful::artful([...], $confirmParams);
```

**检查方法**：搜索 `Artful::artful(` 调用，追踪第二参数来源。

### 2. 空值处理

检查最终发送的 body/query 是否包含不应出现的 `null`/空字段。

**推荐方式**：
- 优先使用 `filter_params()` 函数（artful 库提供）
- 嵌套数组场景补充 `array_filter()`

```php
// ✅ 优先方式 — filter_params
$body = http_build_query(filter_params($payload)->toArray());

// ✅ 嵌套场景 — array_filter
$rocket->mergePayload(array_filter([
    'application_context' => $payload->get('application_context'),
], static fn ($value) => !is_null($value)));
```

**检查位置**：`AddRadarPlugin::getBody()`、`getQueryString()`、业务 Plugin 的 `mergePayload()`。

### 3. Webhook 签名验证

**安全强制**：所有 CallbackPlugin 必须验签。

| Provider | Trait 方法 | 必需配置字段 |
|----------|------------|--------------|
| Stripe | `StripeTrait::verifyStripeWebhookSign()` | `webhook_secret` |
| PayPal | `PaypalTrait::verifyPaypalWebhookSign()` | `webhook_id` + OAuth |
| 微信 | `WechatTrait::verifyWechatSign()` | `mch_secret_cert`、`wechat_public_cert_path`（可预置或运行时拉取） |
| 抖音 | —（在 `CallbackPlugin::verifySign()` 中实现） | `mch_secret_token`、`mch_secret_salt` |
| 银联 | `UnipayTrait::verifyUnipaySign()` | `unipay_public_cert_path` |
| 支付宝 | `AlipayTrait::verifyAlipaySign()` | `alipay_public_cert_path` |

**检查点**：
- CallbackPlugin 是否调用 Trait 提供或 Plugin 自身实现的签名验证方法
- 签名算法是否与官方文档一致（对照 `@see` 链接）
- 配置缺失时抛异常
- 签名为空时抛异常
- 使用 `hash_equals` 防时序攻击

### 4. 数组回调的处理

**仅适用于 Stripe/Wechat/Paypal**（构造 `ServerRequest` 并验签）：

`getCallbackParams()` 处理逻辑：

| 输入类型 | 行为 |
|----------|------|
| `['body' => ..., 'headers' => ...]` | 构造带 headers 的 `ServerRequest`，**会验签** |
| 纯数组（无 headers） | 构造无 headers 的 `ServerRequest`，验签时会抛 `SIGN_EMPTY` |
| `ServerRequestInterface` | 直接使用，验签 |
| `null` | 从 `ServerRequest::fromGlobals()` 获取 |

**结论**：数组回调只有提供完整 `headers` + `body` 才能通过验签；否则验签阶段抛异常。

**Alipay/Douyin/Unipay 不同**：
- `getCallbackParams()` 返回 `Collection`（从 query/parsedBody 取值）
- 直接 merge 到 params，不构造 `ServerRequest`
- CallbackPlugin 从 params 中取值验签

### 5. 加密资源解密（微信）

微信回调的 `resource` 字段需解密：

```php
$body['resource'] = self::decryptWechatResource($body['resource'] ?? [], $config);
```

检查 `CallbackPlugin` 是否调用此方法。

---

## Phase 4: 架构一致性

### 1. 插件管道骨架

**通用骨架**：
```
StartPlugin → [前置插件] → 业务插件 → [后置插件] → ParserPlugin
```

**各 Provider 差异**：

| Provider | 前置插件 | 后置插件 |
|----------|----------|----------|
| Stripe | 无 | `AddRadarPlugin` → `ResponsePlugin` |
| PayPal | `ObtainAccessTokenPlugin` | `AddPayloadBodyPlugin` → `AddRadarPlugin` → `ResponsePlugin` |
| 微信 | 无 | `AddPayloadBodyPlugin` → `AddPayloadSignaturePlugin` → `AddRadarPlugin` → `VerifySignaturePlugin` → `ResponsePlugin` |
| 支付宝 | 无 | `FormatPayloadBizContentPlugin` → `AddPayloadSignaturePlugin` → `AddRadarPlugin` → `ResponsePlugin` |

**注意**：不同 Provider 管道差异较大，不要按固定模板审查。

### 2. mergeCommonPlugins 实现

Provider 类必须实现此方法，返回完整管道：

```php
public function mergeCommonPlugins(array $plugins): array
{
    return array_merge(
        [StartPlugin::class, /* 前置插件 */],
        $plugins,
        [/* 后置插件 */, ParserPlugin::class],
    );
}
```

### 3. Trait 方法复用

新增 Provider 应复用 Trait 方法而非重新实现：

| 功能 | Trait 方法 |
|------|------------|
| URL 构建 | `get{Provider}Url()` |
| 签名验证 | `verify{Provider}WebhookSign()` / `verify{Provider}Sign()` |
| 配置获取 | `ProviderConfigTrait::getProviderConfig()`、`getTenant()` |
| 加密解密 | `WechatTrait::decryptWechatResource()` |

### 4. Provider 常量定义

必须定义 `URL` 常量：

```php
public const URL = [
    Pay::MODE_NORMAL => 'https://api.xxx.com/',
    Pay::MODE_SANDBOX => 'https://sandbox.api.xxx.com/',
    Pay::MODE_SERVICE => 'https://api.xxx.com/',
];
```

特殊常量（如微信）：
- `AUTH_TAG_LENGTH_BYTE`
- `MCH_SECRET_KEY_LENGTH_BYTE`

### 5. Event 调用

callback 方法必须触发事件：

```php
Event::dispatch(new CallbackReceived('provider', clone $request, $params, null));
```

其他方法触发：

```php
Event::dispatch(new MethodCalled('provider', __METHOD__, $order, null));
```

### 6. PHPStan ignore 注释

Trait 静态方法调用需添加注释：

```php
/* @phpstan-ignore-next-line */
self::verifyWechatSign(...);
```

这是 PHPStan 对 Trait 静态调用的已知限制，非代码质量问题。

---

## Phase 5: 官方文档对照

结合代码中的 `@see` 链接验证：

| # | 检查点 |
|---|--------|
| 1 | API 端点 URL 是否与官方一致 |
| 2 | HTTP 方法（GET/POST）是否正确 |
| 3 | 认证 Header 名称、格式是否正确 |
| 4 | 请求/响应字段（必填/可选）是否正确 |
| 5 | 签名算法、拼接顺序是否与官方完全一致 |
| 6 | Base URL（production/sandbox）是否正确 |

---

## Phase 6: 测试覆盖

| # | 检查项 |
|---|--------|
| 1 | 每个 Plugin 有对应测试 |
| 2 | 必填参数缺失的异常测试 |
| 3 | 可选参数缺失的边界测试 |
| 4 | 多租户场景（`_config` 参数） |
| 5 | HTTP client mock，禁止真实 API 调用 |
| 6 | Callback 签名验证的正向/反向测试 |

---

## Phase 7: 文档完整性

| # | 检查项 |
|---|--------|
| 1 | 官方链接可访问且指向正确端点 |
| 2 | 示例代码使用原生 PHP，框架无关 |
| 3 | 侧边栏已更新 |
| 4 | CHANGELOG 已更新 |

---

## 常见误报

### null-safe 操作符

```php
$payload?->get('a', $payload->get('b'))
```

PHP 8.0 的 null-safe 实现了 **full short-circuiting**：当 `$payload` 为 `null` 时，右侧参数不会被求值。这不是 bug。

参考：[PHP RFC nullsafe_operator](https://wiki.php.net/rfc/nullsafe_operator)

---

## 避免重复造轮子

检查新增功能是否在 `yansongda/supports` 或 `yansongda/artful` 中已有实现：

| 功能 | 已有实现 |
|------|----------|
| UUID 生成 | `Str::uuidV4()` |
| 字符串处理 | `Str::*` |
| 集合操作 | `Collection::*` |
| 空值过滤 | `filter_params()` (artful) |
| HTTP 方法获取 | `get_radar_method()` (artful) |
| Body 获取 | `get_radar_body()` (artful) |



## Review 报告模板

以下为提交 review 时的标准报告格式：

````markdown
# PR #{number} Code Review — {Provider} Provider

## 一、总览

| 维度 | 内容 |
|------|------|
| PR | #{number} — {title} |
| 涉及文件 | {n} 个 |
| Review 范围 | 全量代码 + 文档 + 测试 |
| 方法 | 逐文件审查 + 官方文档对照 + 跨 Provider 一致性比对 |

## 二、已确认 Bug（含证据链）

### BUG-{n}: {简要描述}

**严重级别**：{高/中高/中/低}

**位置**：`{file_path}` L{line}

**问题**：
{详细描述问题本质}

**证据链**：
1. 代码现状：`{相关代码片段}`
2. 预期行为：{应该怎样}
3. 实际行为：{实际会怎样}
4. 影响范围：{哪些场景会触发}

**修复建议**：
```php
// 修复方案
```

---

（重复以上格式，每个 Bug 单独一节）

## 三、已排除的误报

### {误报描述}

**结论**：非 Bug

**原因**：{详细解释，附 RFC/文档链接}

## 四、风险项

| # | 风险 | 严重级别 | 文件 | 说明 |
|---|------|----------|------|------|
| RISK-{n} | {风险名} | {级别} | `{file}` | {说明} |

## 五、改进建议

| # | 建议 | 优先级 | 文件 | 说明 |
|---|------|--------|------|------|
| SUG-{n} | {建议名} | {级别} | `{file}` | {说明} |

## 六、官方文档对照验证

| 功能 | 代码中的 URL/算法 | 官方文档 | 是否一致 |
|------|-------------------|----------|----------|
| {功能名} | `{代码实现}` | [官方文档]({url}) | ✅/❌ |

## 七、代码规范检查

| 检查项 | 状态 | 备注 |
|--------|------|------|
| `declare(strict_types=1)` | ✅/❌ | |
| `use` 导入（无内联命名空间） | ✅/❌ | |
| 日志格式 `[Provider][V{n}][Category][Plugin]` | ✅/❌ | |
| 异常常量命名 | ✅/❌ | |
| Plugin/Shortcut 命名规范 | ✅/❌ | |
| 测试覆盖 | ✅/❌ | |

## 八、汇总

| 类别 | 数量 | 详情 |
|------|------|------|
| 已确认 Bug | {n} | {简要列举} |
| 已排除误报 | {n} | {简要列举} |
| 风险项 | {n} | {简要列举} |
| 改进建议 | {n} | {简要列举} |

## 九、结论

{总体评价：代码质量、架构一致性、安全性等。明确给出 Approve / Request Changes 建议及原因。}
````
