# yansongda/pay Copilot 指南

## 项目概述

`yansongda/pay` 是一个 PHP 支付 SDK，为多个支付服务商（支付宝、微信支付、银联、抖音支付、江苏银行、PayPal）提供统一接口。基于 `yansongda/artful` 构建的**插件管道架构**。

## 核心原则

- **安全第一**: 本类库与支付和金钱相关，API 安全是最高优先级。所有回调/Webhook 处理必须实现签名验证，确保数据真实性与完整性。
- **代码一致性**: 新增的 Provider 应遵循现有 Provider 的代码结构和风格。

## 架构

### 插件管道

每次支付操作都通过插件管道流转：

```
StartPlugin → ObtainTokenPlugin → 业务插件 → AddPayloadBodyPlugin → AddRadarPlugin → ResponsePlugin → ParserPlugin
```

- **StartPlugin** — 初始化 `Rocket` 载荷
- **ObtainTokenPlugin** — 获取认证信息（access token、签名等）
- **业务插件** — 设置 API 端点 URL，构建请求载荷
- **AddPayloadBodyPlugin** — 将载荷序列化为 HTTP body
- **AddRadarPlugin** — 构建最终 HTTP 请求（PSR-7），设置认证 header
- **ResponsePlugin** — 校验 HTTP 响应状态码
- **ParserPlugin** — 将原始响应解析为 `Collection`

### 核心抽象

- **`Rocket`** — 在管道中流转的数据载体；包含 params、payload、radar（HTTP 请求）、direction 和 destination（响应）
- **`PluginInterface`** — 每个插件实现 `assembly(Rocket $rocket, Closure $next): Rocket`
- **`ProviderInterface`** — 每个支付服务商实现 `pay`、`query`、`cancel`、`close`、`refund`、`callback`、`success`
- **`ShortcutInterface`** — 快捷方式返回常用操作的插件类数组
- **`Collection`** — 使用 `Yansongda\Supports\Collection` 进行结构化数据访问

### 目录结构

```
src/
├── Contract/            # 接口（ProviderInterface 等）
├── Event/               # 事件类（CallbackReceived、MethodCalled 等）
├── Exception/           # 异常常量（Exception.php）
├── Plugin/
│   ├── {Provider}/
│   │   ├── V{n}/        # 按 API 版本分版（V2、V3 等）
│   │   │   ├── Pay/     # 支付相关插件
│   │   │   ├── AddRadarPlugin.php
│   │   │   ├── ResponsePlugin.php
│   │   │   └── ...
├── Provider/            # 服务商实现（Alipay.php、Wechat.php 等）
├── Service/             # DI 容器注册的服务提供者
├── Shortcut/
│   └── {Provider}/      # 快捷方式类（WebShortcut、QueryShortcut 等）
├── Traits/
├── Functions.php        # 辅助函数（get_{provider}_url、verify_{provider}_sign 等）
└── Pay.php              # 主入口
```

## 编码规范

### PHP 代码风格

- **严格类型**: 每个 PHP 文件必须有 `declare(strict_types=1);`
- **CS Fixer**: 运行 `composer cs-fix` — 使用 `@PhpCsFixer` 规则集
- **导入**: 始终使用 `use` 语句；禁止在代码中使用完整命名空间。开启 `global_namespace_import`（类、常量和函数）
- **多行条件**: `&&` / `||` 运算符放在续行的**开头**，不要放在行尾
- **PHPStan**: 运行 `composer analyse` — 级别 5，使用 `phpstan.neon` 配置

### 命名约定

- **插件**: `{Action}Plugin.php`（如 `PayPlugin`、`RefundPlugin`、`QueryPlugin`、`CapturePlugin`、`CallbackPlugin`）
- **快捷方式**: `{Method}Shortcut.php`（如 `WebShortcut`、`QueryShortcut`、`RefundShortcut`）
- **服务商**: `{ProviderName}.php`（如 `Paypal.php`、`Alipay.php`）
- **服务提供者**: `{ProviderName}ServiceProvider.php`
- **命名空间**: `Yansongda\Pay\Plugin\{Provider}\V{n}\Pay\{Plugin}` — 版本号与服务商的 API 版本一致

### 日志

使用中文日志消息，格式如下：

```php
Logger::debug('[Provider][Version][Category][Plugin] 插件开始装载', ['rocket' => $rocket]);
Logger::info('[Provider][Version][Category][Plugin] 插件装载完毕', ['rocket' => $rocket]);
```

### 异常

- 在 `src/Exception/Exception.php` 中定义异常代码常量
- 使用 `Yansongda\Artful\Exception\*` 异常类（InvalidParamsException、InvalidConfigException 等）
- 使用中文错误消息：`'参数异常: ...'`、`'配置异常: ...'`

### 服务商配置

每个服务商的配置存储在配置数组对应的 key 下：

```php
'paypal' => [
    'default' => [
        'client_id' => '',
        'app_secret' => '',
        'webhook_id' => '',
        'mode' => Pay::MODE_SANDBOX,  // MODE_NORMAL 用于生产环境
    ],
],
```

## 安全要求

- **所有回调/Webhook 处理必须实现签名验证**。禁止直接信任传入的载荷数据，必须验证其来源的真实性。
- 各服务商的签名验证方式不同：
  - 支付宝：本地验证 RSA 签名
  - 微信：本地验证证书签名
  - PayPal：调用 `verify-webhook-signature` API 验证
  - 抖音：本地验证 SHA1 签名
  - 银联：本地验证证书签名
- 回调处理应遵循已有 Provider 的模式：Provider 的 `callback()` 方法传递 `_request`（ServerRequestInterface）和 `_params` 到 CallbackPlugin，由插件负责签名验证。

## 测试

- **框架**: PHPUnit 9.x — 运行 `composer test`
- **测试位置**: `tests/` 目录结构与 `src/` 保持镜像
- **基类**: 继承 `Yansongda\Pay\Tests\TestCase`，其 `setUp()` 中调用 `Pay::config(...)`
- **Mock**: 使用 Mockery 模拟 HTTP 客户端（`Yansongda\Artful\Contract\HttpClientInterface`）
- **配置**: 测试用 PayPal 配置定义在 `tests/TestCase.php` 的 `paypal.default` 中

### 测试模式

```php
class SomePluginTest extends TestCase
{
    protected SomePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->plugin = new SomePlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([...]);
        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        self::assertEquals('expected_url', $result->getPayload()->get('_url'));
    }
}
```

## 文档

- **位置**: `web/docs/v3/{provider}/`
- **格式**: VitePress Markdown
- **必需页面**（每个服务商）: `pay.md`、`query.md`、`refund.md`、`cancel.md`、`close.md`、`callback.md`、`response.md`、`all.md`
- **快速入门**: `web/docs/v3/quick-start/{provider}.md`
- **侧边栏**: 更新 `web/.vitepress/sidebar/v3.js`
- **初始化配置**: 在 `web/docs/v3/quick-start/init.md` 中添加服务商配置块
- **变更日志**: 遵循 `CHANGELOG.md` 中的现有格式 — 按版本号分组
- **代码示例**: 文档中的代码示例应使用原生 PHP，保持框架无关性（不要使用 Laravel 特有的 `collect()` 等辅助函数）

## 新增服务商步骤

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
