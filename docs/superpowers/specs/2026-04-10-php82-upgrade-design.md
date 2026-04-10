# PHP 8.2 升级设计文档

**日期**: 2026-04-10
**作者**: Sisyphus
**状态**: 待实施

---

## 1. 概述

### 1.1 目标

将项目最低 PHP 版本要求从 `>=8.0` 升级到 `>=8.2`，并删除所有已标记为废弃的代码。

### 1.2 背景

- 当前项目支持 PHP 8.0-8.4，CI 已测试 PHP 8.1-8.4
- 项目中存在使用 `#[Deprecated]` 注解标记的废弃代码
- PHP 8.2 引入了动态属性 deprecated 警告等特性，项目代码已兼容

### 1.3 影响范围

- **破坏性变更**: PHP 8.0/8.1 用户无法安装新版本
- **API 变更**: 删除废弃函数、类和命名空间重构

---

## 2. 技术设计

### 2.1 composer.json 改动

```json
{
    "require": {
        "php": ">=8.2"
    }
}
```

### 2.2 删除废弃函数

**文件**: `src/Functions.php`

| 函数名 | 行号 | 替代方案 |
|--------|------|----------|
| `get_alipay_config` | 87-97 | `get_provider_config('alipay', $params)` |
| `get_wechat_config` | 138-148 | `get_provider_config('wechat', $params)` |
| `get_unipay_config` | 488-498 | `get_provider_config('unipay', $params)` |

### 2.3 删除废弃插件类

#### 2.3.1 微信 Transfer 插件（旧 API，已废弃）

删除整个 `src/Plugin/Wechat/V3/Marketing/Transfer/` 目录，包含以下文件：

| 文件路径 |
|----------|
| `CreatePlugin.php` |
| `Batch/QueryByWxPlugin.php` |
| `Batch/QueryPlugin.php` |
| `Detail/QueryByWxPlugin.php` |
| `Detail/QueryPlugin.php` |
| `Receipt/CreatePlugin.php` |
| `Receipt/QueryPlugin.php` |
| `ReceiptDetail/CreatePlugin.php` |
| `ReceiptDetail/QueryPlugin.php` |
| `DownloadReceiptPlugin.php` |

**原因**: 微信支付 API 变更，自 v3.7.12 标记为废弃，将在 v3.8.0 移除。

#### 2.3.2 Wechat StartPlugin

删除 `src/Plugin/Wechat/StartPlugin.php`

**替代方案**: 使用 `Yansongda\Artful\Plugin\StartPlugin`

### 2.4 重命名 MchTransfer → Transfer

将 `src/Plugin/Wechat/V3/Marketing/MchTransfer/` 目录重命名为 `src/Plugin/Wechat/V3/Marketing/Transfer/`

**涉及文件** (7个):

| 文件 | 命名空间变更 |
|------|-------------|
| `CreatePlugin.php` | `MchTransfer` → `Transfer` |
| `QueryPlugin.php` | `MchTransfer` → `Transfer` |
| `QueryByWxPlugin.php` | `MchTransfer` → `Transfer` |
| `CancelPlugin.php` | `MchTransfer` → `Transfer` |
| `InvokeJsapiPlugin.php` | `MchTransfer` → `Transfer` |
| `InvokeAndroidPlugin.php` | `MchTransfer` → `Transfer` |
| `InvokeIosPlugin.php` | `MchTransfer` → `Transfer` |

### 2.5 更新命名空间引用

#### 2.5.1 Shortcut 文件更新

**TransferShortcut.php**:
1. 删除 `use Yansongda\Pay\Plugin\Wechat\V3\Marketing\MchTransfer\CreatePlugin`
2. 删除 `transferPlugins()` 中对旧 `Transfer\CreatePlugin` 的引用（已删除）
3. 将 `mchTransferPlugins()` 改为 `transferPlugins()`，使用重命名后的 `Transfer\CreatePlugin`

**QueryShortcut.php**:
1. 更新 import: `MchTransfer\QueryByWxPlugin` → `Transfer\QueryByWxPlugin`
2. 更新 import: `MchTransfer\QueryPlugin` → `Transfer\QueryPlugin`
3. 删除旧 `Transfer\Detail\QueryPlugin` import（已删除）
4. 将 `mchTransferPlugins()` 改为 `transferPlugins()`

**CancelShortcut.php**:
1. 更新 import: `MchTransfer\CancelPlugin` → `Transfer\CancelPlugin`
2. 将 `mchTransferPlugins()` 改为 `transferPlugins()`

### 2.6 删除测试文件

#### 2.6.1 删除旧 Transfer 测试

删除 `tests/Plugin/Wechat/V3/Marketing/Transfer/` 整个目录（10个文件）

#### 2.6.2 删除 Wechat StartPlugin 测试

删除 `tests/Plugin/Wechat/StartPluginTest.php`

#### 2.6.3 重命名 MchTransfer 测试目录

将 `tests/Plugin/Wechat/V3/Marketing/MchTransfer/` 重命名为 `tests/Plugin/Wechat/V3/Marketing/Transfer/`

更新测试文件命名空间引用：
- `tests/Shortcut/Wechat/TransferShortcutTest.php`
- `tests/Shortcut/Wechat/QueryShortcutTest.php`
- `tests/Shortcut/Wechat/CancelShortcutTest.php`
- `tests/Plugin/Wechat/V3/Marketing/Transfer/*.php`（7个文件）

### 2.7 更新文档

#### 2.7.1 删除废弃章节

**文件**: `web/docs/v3/wechat/all.md`

删除「商家转账到零钱」章节（第 365-414 行），因为这些插件已被删除。

#### 2.7.2 更新 CHANGELOG

新增变更记录到 `CHANGELOG.md`

### 2.8 验证步骤

1. 运行 `composer test` 确保所有测试通过
2. 运行 `composer analyse` 确保静态分析通过
3. 运行 `composer cs-fix` 确保代码风格一致

---

## 3. 变更清单汇总

| 操作类型 | 数量 |
|----------|------|
| 删除废弃函数 | 3 个 |
| 删除废弃插件类 | 11 个 |
| 删除测试文件 | 11 个 |
| 重命名目录（源码） | 1 个 |
| 更新命名空间（源码） | 7 个文件 |
| 更新命名空间（测试） | 7 个文件 |
| 更新 Shortcut 文件 | 3 个 |
| 更新 Shortcut 测试 | 3 个 |
| 更新文档 | 2 个 |
| composer.json 改动 | 1 处 |

---

## 4. 风险评估

| 风险 | 影响 | 缓解措施 |
|------|------|----------|
| 用户使用废弃函数 | 编译错误 | CHANGELOG 明确说明替代方案 |
| 用户使用 Transfer 插件 | 类不存在 | CHANGELOG 说明需自行实现 |
| 用户使用 Wechat StartPlugin | 类不存在 | CHANGELOG 说明替代方案 |
| 依赖包版本不一致 | 无 | composer 会自动处理 |

---

## 5. 回滚方案

如果升级后发现问题：
1. 回退 composer.json 中的 PHP 版本要求
2. 恢复删除的文件（git revert）
3. 恢复目录重命名（git revert）

---

## 6. 后续工作

- 推动依赖包 `yansongda/artful` 和 `yansongda/supports` 同步升级最低 PHP 版本
- 发布 v3.8.0 版本，包含本次破坏性变更