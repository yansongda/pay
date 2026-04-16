---
name: container-dev
description: "Use when local PHP environment is unavailable. Fallback container-based development environment for yansongda/pay project."
---

# Container-based Local Development (Fallback)

当本地没有 PHP 环境时，使用 Apple Container 或 Docker 作为备选方案。

## 使用原则

**优先本地环境**：先检查本地是否有 PHP/composer，有则直接使用。

**容器作为备选**：仅在本地环境不可用时使用容器。

## 快速参考

### 镜像

```
registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine
```

### 常用命令

| 任务 | 命令 |
|------|------|
| 运行测试 | `composer test` |
| PHPStan 分析 | `composer analyse` |
| 代码风格检查 | `composer cs-fix`（**仅检查，不修复**） |
| 代码风格修复 | `php-cs-fixer fix ./src` |
| Composer 更新 | `composer update --with-all-dependencies` |
| 安装依赖 | `composer install` |
| Web 文档开发 | `pnpm web:dev` |
| Web 文档构建 | `pnpm web:build` |

---

## Apple Container

### 基本模板

```bash
container run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  COMMAND
```

### DNS 问题处理

若出现 DNS 解析失败（`198.18.0.x` 地址、连接超时），添加 DNS fix：

```bash
container run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  sh -c "echo 'nameserver 8.8.8.8' > /etc/resolv.conf && COMMAND"
```

### PHP 命令示例

```bash
# 测试（需 DNS fix + COMPOSER_ALLOW_SUPERUSER）
container run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  sh -c "echo 'nameserver 8.8.8.8' > /etc/resolv.conf && COMPOSER_ALLOW_SUPERUSER=1 composer test"

# PHPStan 分析
container run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  sh -c "echo 'nameserver 8.8.8.8' > /etc/resolv.conf && COMPOSER_ALLOW_SUPERUSER=1 composer analyse"

# 代码风格检查（仅查看差异）
container run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  sh -c "echo 'nameserver 8.8.8.8' > /etc/resolv.conf && COMPOSER_ALLOW_SUPERUSER=1 composer cs-fix"

# 代码风格修复
container run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  sh -c "echo 'nameserver 8.8.8.8' > /etc/resolv.conf && COMPOSER_ALLOW_SUPERUSER=1 php-cs-fixer fix ./src"

# Composer update
container run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  sh -c "echo 'nameserver 8.8.8.8' > /etc/resolv.conf && composer update --with-all-dependencies"
```

### Web 文档命令

Web 文档使用 pnpm，镜像中无 Node.js，需使用 Node 镜像或本地运行：

```bash
# 方案1: 本地运行（推荐）
cd web && pnpm web:dev

# 方案2: 使用 Node 容器
docker run --rm -v "$(pwd)/web":/app -w /app \
  node:18-alpine \
  sh -c "npm install -g pnpm && pnpm install && pnpm web:dev"
```

---

## Docker

Docker 命令类似，通常不需要 DNS fix：

```bash
# 测试
docker run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  composer test

# PHPStan 分析
docker run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  composer analyse

# 代码风格检查
docker run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  composer cs-fix

# 代码风格修复
docker run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  php-cs-fixer fix ./src
```

---

## DNS 问题诊断（Apple Container）

**症状**：
- `curl error 28 Connection timed out`
- DNS 解析返回 `198.18.0.x`（基准测试保留地址）

**原因**：Container 系统 DNS 解析器异常。

**解决**：手动写入 `nameserver 8.8.8.8` 到 `/etc/resolv.conf`。

---

## 注意事项

- `COMPOSER_ALLOW_SUPERUSER=1`：容器内运行 composer 脚本必须
- `composer cs-fix`：**仅检查并显示差异**，不自动修复
- `php-cs-fixer fix ./src`：实际修复代码风格
- Apple Container 需先启动：`container system start`
- Docker 需确保 Docker Desktop 或 daemon 已启动
- 容器内网络可能无法访问某些 CDN，测试中可能出现 PHP warning（不影响核心功能）