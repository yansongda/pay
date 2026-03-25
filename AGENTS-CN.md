# AGENTS-CN.md — yansongda/pay 编码代理指南

> 另见：`.github/copilot-instructions.md` 获取完整项目指南。

## 项目概述

PHP 支付 SDK，为支付宝、微信支付、银联、抖音支付、江苏银行、PayPal 和 Stripe 提供统一接口。基于 `yansongda/artful` 的**插件管道架构**。要求 PHP >= 8.0。

## 本地开发环境

如果没有本地 PHP 环境，优先使用 Docker / container（Apple Silicon）：

- 镜像：`registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine`
- 启动命令（`xxx` 替换为宿主机代码目录）：

```bash
docker run -it --rm -p 8080:8080 -v xxx:/www registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine sh
```

```bash
container run -it --rm -p 8080:8080 -v xxx:/www registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine sh
```

容器启动后代码挂载在 `/www`，在该目录下执行所有开发命令。

## 构建 / 检查 / 测试命令

| 操作 | 命令 |
|---|---|
| 安装依赖 | `composer install` |
| 运行全部测试 | `composer test` |
| 运行单个测试文件 | `./vendor/bin/phpunit tests/Path/To/SomeTest.php` |
| 运行单个测试方法 | `./vendor/bin/phpunit --filter testMethodName tests/Path/To/SomeTest.php` |
| 代码风格检查（dry-run） | `composer cs-fix` |
| 代码风格自动修复 | `./vendor/bin/php-cs-fixer fix` |
| 静态分析 | `composer analyse` |
| 带覆盖率测试 | `./vendor/bin/phpunit --coverage-clover coverage.xml` |

CI 在 PHP 8.1-8.4 上运行风格检查（PHP-CS-Fixer + PHPStan）和测试，覆盖三种 DI 容器变体（default/laravel/hyperf）。

## 架构 — 插件管道

每次支付操作通过有序的插件链流转：

```
StartPlugin -> ObtainTokenPlugin -> 业务插件 -> AddPayloadBodyPlugin
-> AddRadarPlugin -> ResponsePlugin -> ParserPlugin
```

- **Rocket** — 在管道中流转的数据载体（params、payload、radar、direction、destination）
- **PluginInterface** — `assembly(Rocket $rocket, Closure $next): Rocket`
- **ProviderInterface** — `pay`、`query`、`cancel`、`close`、`refund`、`callback`、`success`
- **ShortcutInterface** — 返回常用操作的插件类数组

## 代码风格

### 严格类型 & 格式化

- **每个 PHP 文件**必须以 `declare(strict_types=1);` 开头
- 格式化工具：PHP-CS-Fixer，使用 `@PhpCsFixer` 规则集（见 `.php-cs-fixer.php`）
- 静态分析：PHPStan level 5（见 `phpstan.neon`）
- 缩进：4 个空格
- 花括号：PSR-12 — 类/方法声明换行，控制结构同行
- 多行函数参数/数组使用尾随逗号
- 多行条件：`&&` / `||` 放在续行的**开头**
- 单行注释使用 `//`（不用 `#`）
- 禁止 `@author` 注解

### 导入规范

- **始终使用 `use` 语句** — 禁止在代码中使用完整命名空间
- 导入类、函数和常量（开启 `global_namespace_import`）
- 分组顺序：PHP 内置 -> 第三方 -> `Yansongda\Artful` / `Yansongda\Supports` -> `Yansongda\Pay` -> `use function` / `use const`
- 每组内按字母序排列
- 名称冲突时使用 `as` 别名

### 命名约定

| 元素 | 约定 | 示例 |
|---|---|---|
| 类 | `PascalCase` | `Alipay`、`WebShortcut` |
| 插件 | `{Action}Plugin` | `PayPlugin`、`RefundPlugin` |
| 快捷方式 | `{Method}Shortcut` | `WebShortcut`、`QueryShortcut` |
| 服务提供者 | `{Provider}ServiceProvider` | `AlipayServiceProvider` |
| 方法 | `camelCase` | `getPlugins()`、`getPayload()` |
| 变量 | `camelCase` | `$privateKey`、`$publicCert` |
| 常量 | `UPPER_SNAKE_CASE` | `MODE_NORMAL`、`SIGN_ERROR` |
| 命名空间函数 | `snake_case` | `get_tenant()`、`verify_alipay_sign()` |
| 内部参数 | `_` 前缀 | `_config`、`_action`、`_url` |

命名空间：`Yansongda\Pay\Plugin\{Provider}\V{n}\{Category}\{Plugin}` — 版本号与服务商 API 版本一致。

### 类型声明

- 所有方法参数和返回类型必须声明
- 适当使用联合类型（`Collection|MessageInterface|Rocket|null`）
- 可选参数使用可空语法 `?Type`
- 所有类属性必须声明类型
- 仅在原生类型不足时使用 `@var` PHPDoc（如 `/** @var PluginInterface[] */`）

### PHPDoc

- 始终声明 `@throws`，列出所有可能抛出的异常
- 在使用 `__call` / `__callStatic` 的类上使用 `@method`
- 使用 `@see` 链接到外部 API 文档
- 当原生类型提示已足够时，**不要**重复写 `@param` / `@return`
- 注释和文档描述使用**中文**

### 异常处理

- 自定义异常基类：`Yansongda\Pay\Exception\Exception`（包含数字错误码常量）
- 错误码范围：`92xx` 参数、`93xx` 响应、`94xx` 配置、`95xx` 签名、`96xx` 加解密
- 构造函数签名：`(int $code, string $message, mixed $extra, ?Throwable $previous)` — 注意：`$code` 在 `$message` 前面
- 错误消息使用中文，带语义前缀：`'签名异常: ...'`、`'参数异常: ...'`
- 通过 `$extra` 传递上下文（如 `func_get_args()`）便于调试
- 优先抛出异常而非 try/catch — 让调用者处理错误
- 使用 `yansongda/artful` 的异常类：`InvalidParamsException`、`InvalidConfigException` 等

### 日志

```php
Logger::debug('[Provider][Version][Category][Plugin] 插件开始装载', ['rocket' => $rocket]);
Logger::info('[Provider][Version][Category][Plugin] 插件装载完毕', ['rocket' => $rocket]);
```

- 方法入口使用 `debug`，方法完成使用 `info`
- 消息使用中文；前缀格式：`[Provider][Version][Category][Plugin]`

## 测试约定

- **框架**：PHPUnit 9.x + Mockery
- **基类**：继承 `Yansongda\Pay\Tests\TestCase`（处理 `Pay::config()` / `Pay::clear()`）
- **目录结构**：镜像 `src/` — 如 `src/Plugin/Alipay/V3/Pay/PayPlugin.php` -> `tests/Plugin/Alipay/V3/Pay/PayPluginTest.php`
- **方法命名**：`testXxx`（不使用 `@test` 注解）
- **断言**：使用 `self::assert*()`（不用 `$this->assert*()`）
- **类注解**：测试类添加 `@internal` 和 `@coversNothing`
- **HTTP Mock**：使用 Mockery 模拟 `GuzzleHttp\Client`，通过 `Pay::set(HttpClientInterface::class, $http)` 注入

```php
$http = Mockery::mock(Client::class);
$http->shouldReceive('sendRequest')->andReturn(new Response(200, [], '...'));
Pay::set(HttpClientInterface::class, $http);
```

## 安全要求

- **所有回调/Webhook 处理必须验证签名。** 禁止直接信任传入的载荷数据。
- 支付宝：本地 RSA 签名验证
- 微信：本地证书签名验证
- PayPal：调用 `verify-webhook-signature` API 验证
- 抖音：本地 SHA1 签名验证
- 银联：本地证书签名验证

## 新增服务商步骤

1. 在 `src/Plugin/{Provider}/V{n}/` 下创建插件
2. 在 `src/Provider/{Provider}.php` 创建服务商类，实现 `ProviderInterface`
3. 在 `src/Service/{Provider}ServiceProvider.php` 创建服务提供者
4. 在 `src/Shortcut/{Provider}/` 下创建快捷方式
5. 在 `src/Functions.php` 中添加辅助函数（`get_{provider}_url`、`verify_{provider}_sign` 等）
6. 在 `src/Pay.php` 中注册服务商
7. 在 `src/Exception/Exception.php` 中添加异常常量
8. 在 `tests/` 下添加与源码结构对应的测试
9. 在 `web/docs/v3/{provider}/` 下添加文档
10. 更新侧边栏、初始化配置和变更日志
