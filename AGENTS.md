**Generated:** 2026-04-11
**Commit:** 5c4c509
**Branch:** master

# yansongda/pay AGENTS.md

## OVERVIEW
PHP 支付 SDK，支持支付宝、微信、银联、抖音、江苏银行、PayPal 等多服务商。项目基于插件管道架构，重点关注支付流程、回调验签和 Provider 扩展一致性。

## STRUCTURE
```text
src/
├── Contract/
├── Event/
├── Exception/
├── Plugin/{Provider}/V{n}/
├── Provider/
├── Service/
├── Shortcut/{Provider}/
├── Traits/
├── Functions.php
└── Pay.php
```

## WHERE TO LOOK
| 任务 | 位置 | 说明 |
|---|---|---|
| 新增 Provider | `src/Plugin/{Provider}/`, `src/Provider/`, `src/Shortcut/{Provider}/` | 10 步流程起点 |
| 支付逻辑 | `src/Plugin/{Provider}/V{n}/Pay/` | 按服务商版本组织 |
| 回调验证 | `src/Plugin/{Provider}/V{n}/CallbackPlugin.php` | 签名验证逻辑 |
| 测试配置 | `tests/TestCase.php` | 所有 Provider 配置 |
| 文档 | `web/docs/v3/{provider}/` | VitePress 文档 |

## CODE MAP
| 符号 | 类型 | 位置 | 引用 | 角色 |
|---|---|---|---|---|
| `Pay::alipay()` | Method | `src/Pay.php:32` | 高 | 支付宝入口 |
| `Pay::wechat()` | Method | `src/Pay.php:33` | 高 | 微信入口 |
| `Pay::unipay()` | Method | `src/Pay.php:34` | 高 | 银联入口 |
| `Pay::jsb()` | Method | `src/Pay.php:35` | 高 | 江苏银行入口 |
| `Pay::douyin()` | Method | `src/Pay.php:36` | 高 | 抖音入口 |
| `Pay::paypal()` | Method | `src/Pay.php:37` | 高 | PayPal 入口 |
| `Pay::stripe()` | Method | `src/Pay.php:38` | 高 | Stripe 入口 |
| `ProviderInterface` | Interface | `src/Contract/ProviderInterface.php` | 高 | 核心接口 |

## 核心原则
- **安全第一**：与支付和金钱相关，所有回调/Webhook 必须验证签名，禁止直接信任传入数据。
- **代码一致性**：新增 Provider 遵循现有 Provider 的结构和风格。

## COMMANDS
```bash
composer test
composer cs-fix
composer analyse

cd web && pnpm web:dev
cd web && pnpm web:build
```

- 本地开发环境：优先用 Docker/container，镜像 `registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine`
- 测试环境：PHPUnit 9.x + Mockery；测试前需安装 `hyperf/pimple`
- CI 矩阵：PHP 8.1-8.4 + Laravel/Hyperf/Default

## 核心架构
- 插件管道：`StartPlugin → ObtainTokenPlugin → 业务插件 → AddPayloadBodyPlugin → AddRadarPlugin → ResponsePlugin → ParserPlugin`
- `Rocket`（`Yansongda\Artful\Rocket`）承载 params、payload、radar、destination
- `PluginInterface` 实现 `assembly(Rocket $rocket, Closure $next): Rocket`
- `ProviderInterface` 实现 `pay`、`query`、`cancel`、`close`、`refund`、`callback`、`success`
- Provider 通过 `__call` 动态调用 Shortcut，再进入插件管道

## 代码规范
- 每个文件必须有 `declare(strict_types=1);`
- 所有导入必须用 `use`，禁止直接写完整命名空间
- 多行条件中 `&&` / `||` 放在续行开头

### 命名约定
| 类型 | 格式 | 示例 |
|---|---|---|
| 插件 | `{Action}Plugin.php` | `PayPlugin`、`RefundPlugin` |
| 快捷方式 | `{Method}Shortcut.php` | `WebShortcut`、`QueryShortcut` |
| 服务商 | `{ProviderName}.php` | `Paypal.php`、`Alipay.php` |
| 服务提供者 | `{ProviderName}ServiceProvider.php` | `PaypalServiceProvider.php` |
| 命名空间 | `Yansongda\Pay\Plugin\{Provider}\V{n}\Pay\{Plugin}` | 版本号与 API 版本一致 |

### 其他约定
- PHPStan 忽略：`Illuminate\Container\Container`、`Hyperf\Utils\ApplicationContext`、`think\Container`
- 日志使用中文消息：`[Provider][Version][Category][Plugin]`
- 异常在 `src/Exception/Exception.php` 定义常量，使用 `Yansongda\Artful\Exception\*`，消息保持中文

### 辅助函数
| 函数类型 | 主要函数 |
|---|---|
| 配置/租户 | `get_tenant()`、`get_provider_config()` |
| URL 构建 | `get_alipay_url()`、`get_wechat_url()`、`get_unipay_url()` |
| 签名验证 | `verify_alipay_sign()`、`verify_wechat_sign()` |
| 证书处理 | `get_public_cert()`、`get_private_cert()` |
| 微信专用 | `decrypt_wechat_resource()`、`reload_wechat_public_certs()` |

### 测试模式
```php
$rocket = new Rocket();
$rocket->setParams(['out_trade_no' => 'test', 'amount' => 1]);
```

```php
$httpClient = Mockery::mock(HttpClientInterface::class);
$httpClient->shouldReceive('sendRequest')->andReturn(new Response(200, [], '{"code":"SUCCESS"}'));
```

### 文档
- 结构：`web/docs/v3/{provider}/`、`web/docs/v3/quick-start/`、`web/docs/v3/overview/`
- 侧边栏：`web/.vitepress/sidebar/v3.js`
- 变更日志：`CHANGELOG.md` 按版本号分组
- 示例代码使用原生 PHP，保持框架无关性

## 安全要点
**所有回调/Webhook 必须验证签名**。

| Provider | 验证方式 |
|---|---|
| 支付宝 | 本地 RSA 验证 |
| 微信 | 本地证书签名验证 |
| PayPal | 调用 `verify-webhook-signature` API |
| 抖音 | 本地 SHA1 验证 |
| 银联 | 本地证书签名验证 |

回调处理：Provider 的 `callback()` 方法传递 `_request`（`ServerRequestInterface`）到 `CallbackPlugin`，由插件负责验证。

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

## 常见错误
- 忘记 `declare(strict_types=1);`
- 在代码中直接使用 `\Yansongda\Pay\...` 而不用 `use`
- 测试中未 Mock HTTP 客户端导致真实 API 调用
- 回调处理未验证签名直接信任数据
