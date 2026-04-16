---
name: pr-review-provider
description: "Use when reviewing PRs that add or modify a payment Provider in yansongda/pay - covers plugin pipeline, multi-tenant safety, signature verification, docs, and naming conventions."
---

# PR Review Checklist — Payment Provider

yansongda/pay 新增/修改 Provider 时的 Code Review 专用检查清单。基于 Airwallex PR #1140 review 经验沉淀。

## 核心架构速查

```
StartPlugin → ObtainTokenPlugin → 业务插件 → AddPayloadBodyPlugin → AddRadarPlugin → ResponsePlugin → ParserPlugin
```

- `Rocket` 承载 params（用户入参，含 `_config`）、payload（API 请求体）、radar（HTTP 请求）、destination（响应）
- `params` 和 `payload` 是两个不同概念，**绝对不能混淆**

## 必查项（Bug 高发区）

### 1. params vs payload 混淆

**高危**：任何调用 `Artful::artful($plugins, $xxx)` 的地方，第二参数必须是 `params`（含 `_config`），不能是 `payload`。

```php
// ❌ 错误 — payload 不含 _config，多租户必崩
$result = Artful::artful([...], $confirmPayload);

// ✅ 正确 — params 含 _config 租户标识
$result = Artful::artful([...], $confirmParams);
```

**检查方法**：搜索所有 `Artful::artful(` 调用，追踪第二参数来源是 `getParams()` 还是 `getPayload()`。

### 2. 可选字段缺少 array_filter

**中高危**：`mergePayload` 中的可选字段必须用 `array_filter` 包裹，否则 `null` 值会被发送到支付 API。

```php
// ❌ null 值会被序列化发送
$rocket->mergePayload([
    'optional_field' => $payload->get('optional_field'),
]);

// ✅ 正确
$rocket->mergePayload(array_filter([
    'optional_field' => $payload->get('optional_field'),
], static fn ($value) => !is_null($value)));
```

**检查方法**：逐个对比所有业务 Plugin 的 `mergePayload` 调用，确认一致性。

### 3. Webhook 签名验证

**安全强制**：所有回调必须验签。

| 检查点 | 要求 |
|--------|------|
| `CallbackPlugin` 调用了签名验证 | 必须 |
| 签名算法与官方文档一致 | 必须对照 @see 链接验证 |
| `webhook_secret` 缺失时抛异常 | 必须 |
| 签名为空时抛异常 | 必须 |
| 使用 `hash_equals` 防时序攻击 | 必须 |

### 4. 纯数组 callback 不验签（设计限制）

`getCallbackParams()` 传入数组时不经过签名验证，这是项目级设计决策（所有 Provider 一致）。确认文档中有说明即可。

## 一致性检查

### Provider 结构完整性

对照 AGENTS.md "新增 Provider 步骤" 逐项检查：

- [ ] `src/Plugin/{Provider}/V{n}/` — 插件
- [ ] `src/Provider/{Provider}.php` — ProviderInterface 实现
- [ ] `src/Service/{Provider}ServiceProvider.php` — 服务注册
- [ ] `src/Shortcut/{Provider}/` — 快捷方式
- [ ] `src/Functions.php` — 辅助函数（`get_{provider}_url`、`verify_{provider}_sign` 等）
- [ ] `src/Pay.php` — Provider 注册
- [ ] `src/Exception/Exception.php` — 异常常量
- [ ] `tests/` — 与源码结构对应
- [ ] `web/docs/v3/{provider}/` — 文档
- [ ] 侧边栏、配置、CHANGELOG 更新

### 管道一致性

每个 Shortcut 的插件管道应遵循统一模式：

```php
[
    StartPlugin::class,
    ObtainAccessTokenPlugin::class, // 或对应的 token 插件
    业务Plugin::class,
    AddPayloadBodyPlugin::class,
    AddRadarPlugin::class,
    ResponsePlugin::class,
    ParserPlugin::class,
]
```

后置插件（如 PayConfirmPlugin）放在 ParserPlugin 之后。

### 命名与代码规范

| 检查点 | 要求 |
|--------|------|
| `declare(strict_types=1)` | 每个文件 |
| `use` 导入 | 禁止直接写完整命名空间 |
| 日志格式 | `[Provider][Version][Category][Plugin]`，中文消息 |
| 异常常量 | `PARAMS_{PROVIDER}_*`、`CONFIG_{PROVIDER}_*` |
| Plugin 命名 | `{Action}Plugin.php` |
| Shortcut 命名 | `{Method}Shortcut.php` |

## 官方文档对照验证

**必须结合代码中的 `@see` 链接和 docs 中的官方文档链接**：

1. **API 端点 URL** — 每个 Plugin 的 `_url` 是否与官方 API 文档一致
2. **HTTP 方法** — GET/POST 是否正确
3. **认证方式** — Header 名称、格式是否与官方一致
4. **请求/响应字段** — 必填/可选字段是否正确
5. **签名算法** — 算法、拼接顺序是否与官方文档完全一致
6. **Base URL** — production/sandbox URL 是否正确

## 文档检查

- [ ] docs 中的官方链接可访问且指向正确端点（注意 create vs retrieve 混淆）
- [ ] 示例代码使用原生 PHP，框架无关
- [ ] 侧边栏已更新

## 避免重复造轮子

检查新增辅助函数是否在 `yansongda/supports` 中已有实现：
- UUID 生成 → `Str::uuidV4()`
- 字符串处理 → `Str::*`
- 集合操作 → `Collection::*`

## 常见误报

### null-safe 操作符空指针

```php
$payload?->get('a', $payload->get('b'))
```

PHP 8.0 的 null-safe 实现了 **full short-circuiting**：当 `$payload` 为 `null` 时，参数 `$payload->get('b')` **不会被求值**。这不是 bug。

参考：[PHP RFC nullsafe_operator](https://wiki.php.net/rfc/nullsafe_operator)

## 测试覆盖检查

- [ ] 每个 Plugin 有对应测试
- [ ] 必填参数缺失的异常测试
- [ ] 可选参数缺失的边界测试（验证 BUG-2 类问题）
- [ ] 多租户场景（`_config` 参数）
- [ ] HTTP client mock，禁止真实 API 调用
- [ ] Callback 签名验证的正向/反向测试

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
