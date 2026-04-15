---
name: container-dev
description: "Use this when local PHP environment is unavailable. Fallback container-based development environment for yansongda/pay project."
---

# Container-based Local Development (Fallback)

当本地没有 PHP 环境时，使用 Apple Container 或 Docker 作为备选方案。

## 使用原则

**优先本地环境**：先检查本地是否有 PHP/composer，有则直接使用。

**容器作为备选**：仅在本地环境不可用时使用容器（Apple Container 或 Docker）。

## 镜像

```
registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine
```

## Apple Container 命令

### 正常情况（网络正常）

```bash
container run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  YOUR_COMMAND
```

### 网络异常时（DNS 问题）

如果 container 网络出现 DNS 解析失败（如 `198.18.0.x` 地址、连接超时），添加 DNS 配置：

```bash
container run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  sh -c "echo 'nameserver 8.8.8.8' > /etc/resolv.conf && YOUR_COMMAND"
```

### 常用命令示例

```bash
# 运行测试
container run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  sh -c "echo 'nameserver 8.8.8.8' > /etc/resolv.conf && COMPOSER_ALLOW_SUPERUSER=1 composer test"

# Composer update
container run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  sh -c "echo 'nameserver 8.8.8.8' > /etc/resolv.conf && composer update --with-all-dependencies"

# PHPStan 分析
container run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  sh -c "echo 'nameserver 8.8.8.8' > /etc/resolv.conf && COMPOSER_ALLOW_SUPERUSER=1 composer analyse"

# CS-Fixer
container run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  sh -c "echo 'nameserver 8.8.8.8' > /etc/resolv.conf && COMPOSER_ALLOW_SUPERUSER=1 composer cs-fix"
```

## Docker 命令

Docker 命令与 Apple Container 类似，只需将 `container` 替换为 `docker`：

```bash
# 运行测试
docker run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  composer test

# Composer update
docker run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  composer update --with-all-dependencies

# PHPStan 分析
docker run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  composer analyse

# CS-Fixer
docker run --rm -v "$(pwd)":/app -w /app \
  registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine \
  composer cs-fix
```

**注意**：Docker 通常不需要 DNS fix，网络配置更稳定。

## DNS 问题诊断（Apple Container）

**症状**：
- `curl error 28 Connection timed out`
- DNS 解析返回 `198.18.0.x`（基准测试保留地址）
- `nslookup` 显示 Server 为 `192.168.64.1:53`

**原因**：Container 系统 DNS 解析器异常，无法正确解析公网域名。

**解决**：手动写入 `nameserver 8.8.8.8` 到 `/etc/resolv.conf`。

## 注意事项

- 使用 `COMPOSER_ALLOW_SUPERUSER=1` 运行 composer 脚本命令
- 容器内网络可能无法访问某些 CDN，测试中可能出现 PHP warning，不影响核心功能
- Apple Container 需先启动系统服务：`container system start`
- Docker 需确保 Docker Desktop 或 Docker daemon 已启动