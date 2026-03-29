# AGENTS.md — Coding Agent Guide for yansongda/pay

> Any updates to this file must be synchronized to `AGENTS-CN.md`.
>
> See also: `.github/copilot-instructions.md` for the full project guide (in Chinese).

## Project Overview

PHP payment SDK providing a unified interface for Alipay, WeChat Pay, UnionPay, Douyin Pay, JSB, PayPal, and Stripe. Built on a **plugin-pipeline architecture** via `yansongda/artful`. Requires PHP >= 8.0.

## Local Development Environment

If you don't have a local PHP environment, prefer Docker / container (Apple Silicon):

- Image: `registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine`
- Start command (replace `xxx` with the host code directory):

```bash
docker run -it --rm -p 8080:8080 -v xxx:/www registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine sh
```

```bash
container run -it --rm -p 8080:8080 -v xxx:/www registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.3-alpine sh
```

Code is mounted at `/www` inside the container. Run all dev commands there.

## Build / Lint / Test Commands

| Task | Command |
|---|---|
| Install dependencies | `composer install` |
| Run all tests | `composer test` |
| Run a single test file | `./vendor/bin/phpunit tests/Path/To/SomeTest.php` |
| Run a single test method | `./vendor/bin/phpunit --filter testMethodName tests/Path/To/SomeTest.php` |
| Code style check (dry-run) | `composer cs-fix` |
| Code style auto-fix | `./vendor/bin/php-cs-fixer fix` |
| Static analysis | `composer analyse` |
| Test with coverage | `./vendor/bin/phpunit --coverage-clover coverage.xml` |

CI runs style checks (PHP-CS-Fixer + PHPStan) and tests across PHP 8.1-8.4 with three DI container variants (default/laravel/hyperf).

## Architecture — Plugin Pipeline

Every payment operation flows through an ordered plugin chain:

```
StartPlugin -> ObtainTokenPlugin -> BusinessPlugin -> AddPayloadBodyPlugin
-> AddRadarPlugin -> ResponsePlugin -> ParserPlugin
```

- **Rocket** — data carrier flowing through the pipeline (params, payload, radar, direction, destination)
- **PluginInterface** — `assembly(Rocket $rocket, Closure $next): Rocket`
- **ProviderInterface** — `pay`, `query`, `cancel`, `close`, `refund`, `callback`, `success`
- **ShortcutInterface** — returns an array of plugin classes for common operations

## Code Style

### Strict Types & Formatting

- **Every PHP file** must start with `declare(strict_types=1);`
- Formatter: PHP-CS-Fixer with `@PhpCsFixer` ruleset (see `.php-cs-fixer.php`)
- Static analysis: PHPStan level 5 (see `phpstan.neon`)
- Indentation: 4 spaces
- Braces: PSR-12 — next-line for class/method, same-line for control structures
- Trailing commas on multi-line function arguments/arrays
- Multi-line conditions: `&&` / `||` at the **start** of continuation lines
- Single-line comments use `//` (not `#`)
- No `@author` annotations

### Imports

- **Always use `use` statements** — never use fully-qualified names inline
- Import classes, functions, and constants (`global_namespace_import` enabled)
- Group order: PHP built-ins -> third-party -> `Yansongda\Artful` / `Yansongda\Supports` -> `Yansongda\Pay` -> `use function` / `use const`
- Alphabetical within each group
- Use `as` aliases when names collide

### Naming Conventions

| Element | Convention | Example |
|---|---|---|
| Classes | `PascalCase` | `Alipay`, `WebShortcut` |
| Plugins | `{Action}Plugin` | `PayPlugin`, `RefundPlugin` |
| Shortcuts | `{Method}Shortcut` | `WebShortcut`, `QueryShortcut` |
| Service providers | `{Provider}ServiceProvider` | `AlipayServiceProvider` |
| Methods | `camelCase` | `getPlugins()`, `getPayload()` |
| Variables | `camelCase` | `$privateKey`, `$publicCert` |
| Constants | `UPPER_SNAKE_CASE` | `MODE_NORMAL`, `SIGN_ERROR` |
| Namespace functions | `snake_case` | `get_tenant()`, `verify_alipay_sign()` |
| Internal params | `_` prefix | `_config`, `_action`, `_url` |

Namespace: `Yansongda\Pay\Plugin\{Provider}\V{n}\{Category}\{Plugin}` — version number matches the provider's API version.

### Type Declarations

- All method parameters and return types must be declared
- Use union types (`Collection|MessageInterface|Rocket|null`) where appropriate
- Use nullable syntax `?Type` for optional parameters
- All class properties must have type declarations
- Use `@var` PHPDoc only when native types are insufficient (e.g., `/** @var PluginInterface[] */`)

### PHPDoc

- Always declare `@throws` for every possible exception
- Use `@method` on classes with `__call` / `__callStatic`
- Use `@see` to link to external API docs
- Do NOT duplicate `@param` / `@return` when native type hints suffice
- Comments and docblock descriptions in **Chinese**

### Error Handling

- Custom exception base: `Yansongda\Pay\Exception\Exception` (with numeric error code constants)
- Error code ranges: `92xx` params, `93xx` response, `94xx` config, `95xx` sign, `96xx` decrypt
- Constructor signature: `(int $code, string $message, mixed $extra, ?Throwable $previous)` — note: `$code` before `$message`
- Error messages in Chinese with semantic prefixes: `'签名异常: ...'`, `'参数异常: ...'`
- Pass context via `$extra` (e.g., `func_get_args()`) for debugging
- Prefer throwing exceptions over try/catch — let callers handle errors
- Use exception classes from `yansongda/artful`: `InvalidParamsException`, `InvalidConfigException`, etc.

### Logging

```php
Logger::debug('[Provider][Version][Category][Plugin] 插件开始装载', ['rocket' => $rocket]);
Logger::info('[Provider][Version][Category][Plugin] 插件装载完毕', ['rocket' => $rocket]);
```

- `debug` at method entry, `info` at method completion
- Messages in Chinese; prefix format: `[Provider][Version][Category][Plugin]`

## Testing Conventions

- **Framework**: PHPUnit 9.x + Mockery
- **Base class**: extend `Yansongda\Pay\Tests\TestCase` (handles `Pay::config()` / `Pay::clear()`)
- **Directory structure**: mirrors `src/` — e.g., `src/Plugin/Alipay/V3/Pay/PayPlugin.php` -> `tests/Plugin/Alipay/V3/Pay/PayPluginTest.php`
- **Method naming**: `testXxx` (no `@test` annotation)
- **Assertions**: use `self::assert*()` (not `$this->assert*()`)
- **Class annotations**: `@internal` and `@coversNothing` on test classes
- **HTTP mocking**: Mockery on `GuzzleHttp\Client`, inject via `Pay::set(HttpClientInterface::class, $http)`
- **Code quality**: Test code (`tests/` directory) is **exempt** from PHPStan static analysis and PHP-CS-Fixer style checks

```php
$http = Mockery::mock(Client::class);
$http->shouldReceive('sendRequest')->andReturn(new Response(200, [], '...'));
Pay::set(HttpClientInterface::class, $http);
```

## Security Requirements

- **All callback/webhook handlers must verify signatures.** Never trust incoming payload without verification.
- Alipay: local RSA signature verification
- WeChat: local certificate-based signature verification
- PayPal: API call to `verify-webhook-signature`
- Douyin: local SHA1 signature verification
- UnionPay: local certificate-based signature verification

## Adding a New Provider

1. Create plugins under `src/Plugin/{Provider}/V{n}/`
2. Create provider class at `src/Provider/{Provider}.php` implementing `ProviderInterface`
3. Create service provider at `src/Service/{Provider}ServiceProvider.php`
4. Create shortcuts under `src/Shortcut/{Provider}/`
5. Add helper functions in `src/Functions.php` (`get_{provider}_url`, `verify_{provider}_sign`, etc.)
6. Register the provider in `src/Pay.php`
7. Add error code constants in `src/Exception/Exception.php`
8. Add mirrored tests under `tests/`
9. Add docs under `web/docs/v3/{provider}/`
10. Update sidebar, init config, and CHANGELOG
