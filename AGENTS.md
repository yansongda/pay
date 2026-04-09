# yansongda/pay AGENTS.md

PHP 支付 SDK，支持支付宝、微信、银联、抖音、江苏银行、PayPal 等多服务商。基于插件管道架构。

## 核心原则

- **安全第一**：与支付和金钱相关，所有回调/Webhook 必须验证签名，禁止直接信任传入数据
- **代码一致性**：新增 Provider 遵循现有 Provider 的结构和风格

## 开发命令

```bash
composer test              # 运行测试
composer cs-fix            # 代码风格检查（仅检查，不修复）
composer analyse           # PHPStan 静态分析（level 5）

cd web && pnpm web:dev     # 文档开发
cd web && pnpm web:build   # 文档构建
```

## 本地开发环境

如果没有 PHP 开发环境，优先使用 Docker 或 container(Apple Silicon)：

- **镜像**：`registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine`
- **启动命令**（`xxx` 替换为宿主机项目目录）：

```bash
# Docker
docker run -it --rm -p 8080:8080 -v xxx:/www registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine sh

# Orbstack（Apple Silicon 推荐）
container run -it --rm -p 8080:8080 -v xxx:/www registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine sh
```

容器启动后代码挂载在 `/www`，在该目录下执行所有开发命令。

## 测试环境

- PHPUnit 9.x + Mockery
- 测试前需安装依赖：`composer require hyperf/pimple`
- CI 矩阵：PHP 8.1-8.4 + Laravel/Hyperf/Default 三种框架
- `tests/TestCase.php` 包含所有 Provider 测试配置和证书

## 目录结构

```
src/
├── Contract/            # 接口（ProviderInterface 等）
├── Event/               # 事件类（CallbackReceived、MethodCalled 等）
├── Exception/           # 异常常量（Exception.php）
├── Plugin/
│   └── {Provider}/
│       └── V{n}/        # 按 API 版本分版（V2、V3 等）
│           ├── Pay/     # 支付相关插件
│           ├── AddRadarPlugin.php
│           ├── ResponsePlugin.php
│           └── ...
├── Provider/            # 服务商实现（Alipay.php、Wechat.php 等）
├── Service/             # DI 容器注册的服务提供者
├── Shortcut/
│   └── {Provider}/      # 快捷方式类（WebShortcut、QueryShortcut 等）
├── Traits/
├── Functions.php        # 辅助函数
└── Pay.php              # 主入口
```

## 核心架构

### 插件管道

每次支付操作流转：`StartPlugin → ObtainTokenPlugin → 业务插件 → AddPayloadBodyPlugin → AddRadarPlugin → ResponsePlugin → ParserPlugin`

- **Rocket** (`Yansongda\Artful\Rocket`) — 管道数据载体，包含 params、payload、radar、destination
- **PluginInterface** — 实现 `assembly(Rocket $rocket, Closure $next): Rocket`
- **ProviderInterface** — 实现 `pay`、`query`、`cancel`、`close`、`refund`、`callback`、`success`
- **ShortcutInterface** — 实现 `getPlugins(array $params): array`，返回操作插件链

### Provider 方法映射

Provider 通过 `__call` 动态调用 Shortcut：

```php
// Pay::alipay()->web(...) 实际调用
Alipay::__call('web', $params) 
  → 查找 Shortcut\Alipay\WebShortcut 
  → 获取插件数组 
  → 执行插件管道
```

## 代码规范

### 必须遵守

- **每个文件必须有 `declare(strict_types=1);`**
- **所有导入必须用 `use` 语句**，禁止代码中使用完整命名空间
- **多行条件**：`&&` / `||` 放在续行**开头**

```php
// 正确
if ($a
    && $b) {
}

// 错误
if ($a &&
    $b) {
}
```

### PHPStan 忽略规则

`phpstan.neon` 配置了以下忽略，不要删除：
- `Illuminate\Container\Container`
- `Hyperf\Utils\ApplicationContext`
- `think\Container`

### 命名约定

| 类型 | 格式 | 示例 |
|-----|------|------|
| 插件 | `{Action}Plugin.php` | `PayPlugin`, `RefundPlugin`, `QueryPlugin`, `CallbackPlugin` |
| 快捷方式 | `{Method}Shortcut.php` | `WebShortcut`, `QueryShortcut`, `RefundShortcut` |
| 服务商 | `{ProviderName}.php` | `Paypal.php`, `Alipay.php` |
| 服务提供者 | `{ProviderName}ServiceProvider.php` | `PaypalServiceProvider.php` |
| 命名空间 | `Yansongda\Pay\Plugin\{Provider}\V{n}\Pay\{Plugin}` | 版本号与 API 版本一致 |

### 日志格式

使用中文日志消息：

```php
Logger::debug('[Provider][Version][Category][Plugin] 插件开始装载', ['rocket' => $rocket]);
Logger::info('[Provider][Version][Category][Plugin] 插件装载完毕', ['rocket' => $rocket]);
```

### 异常处理

- 在 `src/Exception/Exception.php` 定义异常代码常量
- 使用 `Yansongda\Artful\Exception\*` 异常类（InvalidParamsException、InvalidConfigException 等）
- 使用中文错误消息：`'参数异常: ...'`、`'配置异常: ...'`

## 辅助函数 (Functions.php)

| 函数类型 | 主要函数 |
|---------|---------|
| 配置/租户 | `get_tenant()`, `get_provider_config()` |
| URL 构建 | `get_alipay_url()`, `get_wechat_url()`, `get_unipay_url()` 等 |
| 签名验证 | `verify_alipay_sign()`, `verify_wechat_sign()` 等 |
| 证书处理 | `get_public_cert()`, `get_private_cert()` |
| 微信专用 | `decrypt_wechat_resource()`, `reload_wechat_public_certs()` |

URL 函数根据配置的 `mode` (MODE_NORMAL/MODE_SANDBOX/MODE_SERVICE) 自动选择端点。

## 测试模式

### 插件测试

```php
public function testNormal()
{
    $rocket = new Rocket();
    $rocket->setParams(['out_trade_no' => 'test', 'amount' => 1]);
    
    $plugin = new SomePlugin();
    $result = $plugin->assembly($rocket, fn($r) => $r);
    
    self::assertEquals('expected_url', $result->getPayload()->get('_url'));
}
```

### Mock HTTP

```php
$httpClient = Mockery::mock(HttpClientInterface::class);
$httpClient->shouldReceive('sendRequest')
    ->andReturn(new Response(200, [], '{"code":"SUCCESS"}'));
```

## 文档

### 结构

```
web/docs/v3/
├── {provider}/        # pay.md, query.md, refund.md, cancel.md, close.md, callback.md, response.md, all.md
├── quick-start/       # init.md, {provider}.md
└── overview/          # 概述和规划
```

### 文档规范

- 格式：VitePress Markdown
- 侧边栏：`web/.vitepress/sidebar/v3.js`
- 变更日志：`CHANGELOG.md` 按版本号分组
- 代码示例：使用原生 PHP，保持框架无关性（不要用 Laravel 特有的 `collect()` 等）

## 新增 Provider 步骤

1. 在 `src/Plugin/{Provider}/V{n}/` 下创建插件
2. 在 `src/Provider/{Provider}.php` 下创建服务商类，实现 `ProviderInterface`
3. 在 `src/Service/{Provider}ServiceProvider.php` 下创建服务提供者
4. 在 `src/Shortcut/{Provider}/` 下创建快捷方式
5. 在 `src/Functions.php` 中添加辅助函数（`get_{provider}_url`、`verify_{provider}_sign` 等）
6. 在 `src/Pay.php` 中注册服务商
7. 在 `src/Exception/Exception.php` 中添加异常常量
8. 在 `tests/` 下添加与源码结构对应的测试
9. 在 `web/docs/v3/{provider}/` 下添加文档
10. 更新侧边栏、初始化配置和变更日志

## 安全要点

**所有回调/Webhook 必须验证签名**。各 Provider 签名验证方式：

| Provider | 验证方式 |
|----------|---------|
| 支付宝 | 本地 RSA 验证 |
| 微信 | 本地证书签名验证 |
| PayPal | 调用 `verify-webhook-signature` API |
| 抖音 | 本地 SHA1 验证 |
| 银联 | 本地证书签名验证 |

回调处理：Provider 的 `callback()` 方法传递 `_request`（ServerRequestInterface）到 CallbackPlugin，由插件负责验证。

## 常见错误

- 忘记 `declare(strict_types=1);`
- 在代码中直接使用 `\Yansongda\Pay\...` 而不用 `use`
- 测试中未 Mock HTTP 客户端导致真实 API 调用
- 回调处理未验证签名直接信任数据