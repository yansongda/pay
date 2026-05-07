---
name: dev-guide
description: Use when developing, testing, or adding new providers to yansongda/pay project. Covers commands, code standards, and provider implementation guide.
---

# yansongda/pay 开发指南

## 开发命令

**对 `src/` PHP 代码的任何修改，需运行以下三项检查：**

```bash
composer cs-fix && composer analyse && composer test
```

### 测试
```bash
composer test       # PHPUnit 11.x + Mockery 1.6
```
- 测试前需安装 `hyperf/pimple`
- Mock HTTP 客户端避免真实请求

### 代码风格
```bash
composer cs-fix     # 代码格式化检查（dry-run）
```

### 静态分析
```bash
composer analyse    # PHPStan
```

### 文档
```bash
cd web && pnpm web:dev    # 文档开发
cd web && pnpm web:build  # 文档构建
cd web && pnpm web:serve  # 本地预览
```

### 本地开发环境
优先本地 PHP 环境；若无 PHP，使用 Container 作为备选，详见 `container-dev` Skill。

### CI 矩阵
PHP 8.2-8.5 + Laravel/Hyperf/Default

---

## 代码规范

- `declare(strict_types=1);` 必须
- `use` 导入，禁止 `\Yansongda\Pay\...`
- 日志/异常消息用中文
- 命名：`{Action}Plugin.php`、`{Action}Shortcut.php`、`{Provider}Trait.php`

---

## 新增 Provider 流程

### 6 阶段
1. **配置**：`src/Config/{Provider}Config.php`
2. **核心类**：`src/Provider/{Provider}.php` + `src/Service/{Provider}ServiceProvider.php`（继承 AbstractServiceProvider）
3. **插件**：`src/Plugin/{Provider}/V{n}/`（含 CallbackPlugin）
4. **快捷方式**：`src/Shortcut/{Provider}/{Action}Shortcut.php`
5. **Trait**：`src/Traits/{Provider}Trait.php`（`get{Provider}Url`、`verify{Provider}Sign`）
6. **注册**：`Pay.php` + `Config.php` 映射 + `Exception.php` 常量 + 测试 + 文档

---

## 常见错误

- 忽略 `declare(strict_types=1);`
- 直接写完整命名空间
- 回调未验签
- 测试未 Mock HTTP 客户端
