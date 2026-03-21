# Copilot Instructions for yansongda/pay

## Project Overview

`yansongda/pay` is a PHP payment SDK that provides a unified interface for multiple payment providers (Alipay, WeChat Pay, UnionPay, Douyin Pay, JSB, PayPal). It uses a **plugin pipeline architecture** powered by `yansongda/artful`.

## Architecture

### Plugin Pipeline

Every payment operation flows through a plugin pipeline:

```
StartPlugin → ObtainTokenPlugin → BusinessPlugin → AddPayloadBodyPlugin → AddRadarPlugin → ResponsePlugin → ParserPlugin
```

- **StartPlugin** — initializes the `Rocket` payload
- **ObtainTokenPlugin** — obtains authentication (access tokens, signatures, etc.)
- **Business Plugins** — set the API endpoint URL, build request payload
- **AddPayloadBodyPlugin** — serializes the payload into the HTTP body
- **AddRadarPlugin** — builds the final HTTP request (PSR-7), sets auth headers
- **ResponsePlugin** — validates HTTP response status codes
- **ParserPlugin** — parses the raw response into a `Collection`

### Key Abstractions

- **`Rocket`** — the data carrier flowing through the pipeline; holds params, payload, radar (HTTP request), direction, and destination (response)
- **`PluginInterface`** — each plugin implements `assembly(Rocket $rocket, Closure $next): Rocket`
- **`ProviderInterface`** — each payment provider implements `pay`, `query`, `cancel`, `close`, `refund`, `callback`, `success`
- **`ShortcutInterface`** — shortcuts return an array of plugin classes for common operations
- **`Collection`** — `Yansongda\Supports\Collection` is used for structured data access throughout

### Directory Layout

```
src/
├── Contract/            # Interfaces (ProviderInterface, etc.)
├── Event/               # Event classes (CallbackReceived, MethodCalled, etc.)
├── Exception/           # Exception constants (Exception.php)
├── Plugin/
│   ├── {Provider}/
│   │   ├── V{n}/        # Versioned by API version (V2, V3, etc.)
│   │   │   ├── Pay/     # Payment-related plugins
│   │   │   ├── AddRadarPlugin.php
│   │   │   ├── ResponsePlugin.php
│   │   │   └── ...
├── Provider/            # Provider implementations (Alipay.php, Wechat.php, etc.)
├── Service/             # Service providers for DI container registration
├── Shortcut/
│   └── {Provider}/      # Shortcut classes (WebShortcut, QueryShortcut, etc.)
├── Traits/
├── Functions.php        # Helper functions (get_{provider}_url, verify_{provider}_sign, etc.)
└── Pay.php              # Main entry point
```

## Coding Standards

### PHP Style

- **Strict types**: Every PHP file must have `declare(strict_types=1);`
- **CS Fixer**: Run `composer cs-fix` — uses `@PhpCsFixer` ruleset
- **Imports**: Always use `use` statements; never use FQCNs inline. Enable `global_namespace_import` for classes, constants, and functions
- **Multi-line conditions**: Place `&&` / `||` operators at the **start** of continuation lines, not at the end
- **PHPStan**: Run `composer analyse` — level 5, `phpstan.neon` config

### Naming Conventions

- **Plugins**: `{Action}Plugin.php` (e.g., `PayPlugin`, `RefundPlugin`, `QueryPlugin`, `CapturePlugin`, `CallbackPlugin`)
- **Shortcuts**: `{Method}Shortcut.php` (e.g., `WebShortcut`, `QueryShortcut`, `RefundShortcut`)
- **Providers**: `{ProviderName}.php` (e.g., `Paypal.php`, `Alipay.php`)
- **Service providers**: `{ProviderName}ServiceProvider.php`
- **Namespaces**: `Yansongda\Pay\Plugin\{Provider}\V{n}\Pay\{Plugin}` — version matches the provider's API version

### Logging

Use Chinese log messages with the pattern:

```php
Logger::debug('[Provider][Version][Category][Plugin] 插件开始装载', ['rocket' => $rocket]);
Logger::info('[Provider][Version][Category][Plugin] 插件装载完毕', ['rocket' => $rocket]);
```

### Exceptions

- Define exception code constants in `src/Exception/Exception.php`
- Use `Yansongda\Artful\Exception\*` exception classes (InvalidParamsException, InvalidConfigException, etc.)
- Provide Chinese error messages: `'参数异常: ...'`, `'配置异常: ...'`

### Provider Config

Each provider config is stored under its key in the config array:

```php
'paypal' => [
    'default' => [
        'client_id' => '',
        'app_secret' => '',
        'mode' => Pay::MODE_SANDBOX,  // MODE_NORMAL for production
    ],
],
```

## Testing

- **Framework**: PHPUnit 9.x — run with `composer test`
- **Test location**: `tests/` mirrors `src/` structure
- **Base class**: Extend `Yansongda\Pay\Tests\TestCase` which calls `Pay::config(...)` in `setUp()`
- **Mocking**: Use Mockery for HTTP client mocking (`Yansongda\Artful\Contract\HttpClientInterface`)
- **Config**: Test PayPal config is defined in `tests/TestCase.php` under `paypal.default`

### Test Patterns

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

## Documentation

- **Location**: `web/docs/v3/{provider}/`
- **Format**: VitePress Markdown
- **Required pages** per provider: `pay.md`, `query.md`, `refund.md`, `cancel.md`, `close.md`, `callback.md`, `response.md`, `all.md`
- **Quick start**: `web/docs/v3/quick-start/{provider}.md`
- **Sidebar**: Update `web/.vitepress/sidebar/v3.js`
- **Init config**: Add provider config block to `web/docs/v3/quick-start/init.md`
- **CHANGELOG**: Follow existing format in `CHANGELOG.md` — group changes under version header

## Adding a New Provider

1. Create plugins under `src/Plugin/{Provider}/V{n}/`
2. Create provider class under `src/Provider/{Provider}.php` implementing `ProviderInterface`
3. Create service provider under `src/Service/{Provider}ServiceProvider.php`
4. Create shortcuts under `src/Shortcut/{Provider}/`
5. Add helper functions to `src/Functions.php` (`get_{provider}_url`, `get_{provider}_access_token`, etc.)
6. Register the provider in `src/Pay.php`
7. Add exception constants to `src/Exception/Exception.php`
8. Add tests mirroring the source structure under `tests/`
9. Add documentation under `web/docs/v3/{provider}/`
10. Update sidebar, init config, and CHANGELOG
