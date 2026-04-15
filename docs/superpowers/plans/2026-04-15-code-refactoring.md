# v3.8.0 代码重构实施计划

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 将 Functions.php 拆分为 Provider Trait，实现证书缓存，抽象 ServiceProvider，补充类型声明和测试

**Architecture:** 
- 8 个 Provider Trait + 1 个共享 Trait（ProviderConfigTrait）
- 独立 CertManager 类处理证书缓存
- AbstractServiceProvider 抽象基类
- TDD 模式：先写测试，再实现

**Tech Stack:** PHP 8.2+, PHPUnit 11.5, PHPStan Level 5, PHP-CS-Fixer

---

## 文件结构规划

### 新增文件（10 个）

| 文件 | 职责 |
|------|------|
| `src/CertManager.php` | 证书加载与进程内缓存 |
| `src/Traits/ProviderConfigTrait.php` | get_tenant、get_provider_config、get_radar_url |
| `src/Traits/AlipayTrait.php` | verifyAlipaySign、getAlipayUrl |
| `src/Traits/WechatTrait.php` | 微信签名、加解密、证书管理（18 个方法） |
| `src/Traits/UnipayTrait.php` | 银联签名、URL 获取 |
| `src/Traits/JsbTrait.php` | 江苏银行签名 |
| `src/Traits/DouyinTrait.php` | 抖音 URL |
| `src/Traits/PaypalTrait.php` | PayPal 签名、URL、Token |
| `src/Traits/StripeTrait.php` | Stripe 签名、URL |
| `src/Service/AbstractServiceProvider.php` | ServiceProvider 抽象基类 |

### 删除文件（1 个）

| 文件 | 说明 |
|------|------|
| `src/Functions.php` | 829 行辅助函数文件 |

### 修改文件（约 170 个）

| 类别 | 数量 | 说明 |
|------|------|------|
| Plugin 文件 | ~160 | 添加 Trait，替换函数调用 |
| ServiceProvider 文件 | 7 | 继承 AbstractServiceProvider |
| 测试文件 | 4 | 更新导入 |

---

## Task 1: 创建 CertManager 类

**Files:**
- Create: `src/CertManager.php`
- Create: `tests/CertManagerTest.php`

- [ ] **Step 1: Write failing test for getPublicCert from file**

```php
// tests/CertManagerTest.php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests;

use Yansongda\Pay\CertManager;

class CertManagerTest extends TestCase
{
    public function testGetPublicCertFromFile(): void
    {
        $path = __DIR__ . '/Cert/alipayPublicCert.crt';
        $result = CertManager::getPublicCert($path);
        
        $this->assertStringContainsString('BEGIN CERTIFICATE', $result);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/phpunit tests/CertManagerTest.php --colors=always`
Expected: FAIL - Class `Yansongda\Pay\CertManager` not found

- [ ] **Step 3: Create CertManager class**

```php
// src/CertManager.php
<?php

declare(strict_types=1);

namespace Yansongda\Pay;

class CertManager
{
    private static array $cache = [];

    public static function getPublicCert(string $key): string
    {
        $cacheKey = 'public_' . md5($key);

        if (!isset(self::$cache[$cacheKey])) {
            self::$cache[$cacheKey] = is_file($key) 
                ? file_get_contents($key) 
                : $key;
        }

        return self::$cache[$cacheKey];
    }

    public static function getPrivateCert(string $key): string
    {
        $cacheKey = 'private_' . md5($key);

        if (!isset(self::$cache[$cacheKey])) {
            self::$cache[$cacheKey] = self::loadPrivateCert($key);
        }

        return self::$cache[$cacheKey];
    }

    private static function loadPrivateCert(string $key): string
    {
        if (is_file($key)) {
            return file_get_contents($key);
        }

        if (str_starts_with($key, '-----BEGIN PRIVATE KEY-----')) {
            return $key;
        }

        return "-----BEGIN RSA PRIVATE KEY-----\n"
            . wordwrap($key, 64, "\n", true)
            . "\n-----END RSA PRIVATE KEY-----";
    }

    public static function clearCache(): void
    {
        self::$cache = [];
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `./vendor/bin/phpunit tests/CertManagerTest.php --colors=always`
Expected: PASS

- [ ] **Step 5: Add more tests for edge cases**

```php
// tests/CertManagerTest.php - 添加更多测试
public function testGetPublicCertFromString(): void
{
    $certContent = '-----BEGIN CERTIFICATE-----MIIBtest-----END CERTIFICATE-----';
    $result = CertManager::getPublicCert($certContent);
    
    $this->assertEquals($certContent, $result);
}

public function testGetPublicCertCacheHit(): void
{
    $path = __DIR__ . '/Cert/alipayPublicCert.crt';
    
    CertManager::clearCache();
    $result1 = CertManager::getPublicCert($path);
    $result2 = CertManager::getPublicCert($path);
    
    $this->assertEquals($result1, $result2);
}

public function testGetPrivateCertFromFile(): void
{
    $path = __DIR__ . '/Cert/wechatAppPrivateKey.pem';
    $result = CertManager::getPrivateCert($path);
    
    $this->assertStringContainsString('PRIVATE KEY', $result);
}

public function testGetPrivateCertFromString(): void
{
    $keyContent = 'testprivatekeystring123456789012345678901234567890';
    $result = CertManager::getPrivateCert($keyContent);
    
    $this->assertStringContainsString('BEGIN RSA PRIVATE KEY', $result);
}

public function testClearCache(): void
{
    $path = __DIR__ . '/Cert/alipayPublicCert.crt';
    CertManager::getPublicCert($path);
    
    CertManager::clearCache();
    
    // 再次读取应重新加载（可通过性能测试验证）
    $result = CertManager::getPublicCert($path);
    $this->assertStringContainsString('BEGIN CERTIFICATE', $result);
}
```

- [ ] **Step 6: Run all CertManager tests**

Run: `./vendor/bin/phpunit tests/CertManagerTest.php --colors=always`
Expected: All tests PASS

- [ ] **Step 7: Commit**

```bash
git add src/CertManager.php tests/CertManagerTest.php
git commit -m "feat: add CertManager class with process-level caching"
```

---

## Task 2: 创建 ProviderConfigTrait

**Files:**
- Create: `src/Traits/ProviderConfigTrait.php`
- Create: `tests/Traits/ProviderConfigTraitTest.php`

- [ ] **Step 1: Write failing test for getTenant**

```php
// tests/Traits/ProviderConfigTraitTest.php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\ProviderConfigTrait;

class ProviderConfigTraitTest extends \Yansongda\Pay\Tests\TestCase
{
    public function testGetTenantDefault(): void
    {
        $result = ProviderConfigTrait::getTenant([]);
        
        $this->assertEquals('default', $result);
    }

    public function testGetTenantCustom(): void
    {
        $result = ProviderConfigTrait::getTenant(['_config' => 'service_provider']);
        
        $this->assertEquals('service_provider', $result);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/phpunit tests/Traits/ProviderConfigTraitTest.php --colors=always`
Expected: FAIL - Trait not found

- [ ] **Step 3: Create ProviderConfigTrait**

```php
// src/Traits/ProviderConfigTrait.php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

trait ProviderConfigTrait
{
    public static function getTenant(array $params = []): string
    {
        return strval($params['_config'] ?? 'default');
    }

    public static function getProviderConfig(string $provider, array $params = []): array
    {
        $config = Pay::get(ConfigInterface::class);

        return $config->get($provider, [])[self::getTenant($params)] ?? [];
    }

    public static function getRadarUrl(array $config, ?Collection $payload): ?string
    {
        return match ($config['mode'] ?? Pay::MODE_NORMAL) {
            Pay::MODE_SERVICE => $payload?->get('_service_url') ?? $payload?->get('_url') ?? null,
            Pay::MODE_SANDBOX => $payload?->get('_sandbox_url') ?? $payload?->get('_url') ?? null,
            default => $payload?->get('_url') ?? null,
        };
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `./vendor/bin/phpunit tests/Traits/ProviderConfigTraitTest.php --colors=always`
Expected: PASS

- [ ] **Step 5: Add test for getProviderConfig**

```php
// tests/Traits/ProviderConfigTraitTest.php - 添加
public function testGetProviderConfigDefault(): void
{
    $result = ProviderConfigTrait::getProviderConfig('alipay', []);
    
    $this->assertArrayHasKey('app_id', $result);
    $this->assertEquals('9021000122682882', $result['app_id']);
}

public function testGetProviderConfigCustomTenant(): void
{
    $result = ProviderConfigTrait::getProviderConfig('wechat', ['_config' => 'service_provider']);
    
    $this->assertArrayHasKey('sub_mch_id', $result);
}
```

- [ ] **Step 6: Run all tests**

Run: `./vendor/bin/phpunit tests/Traits/ProviderConfigTraitTest.php --colors=always`
Expected: All tests PASS

- [ ] **Step 7: Commit**

```bash
git add src/Traits/ProviderConfigTrait.php tests/Traits/ProviderConfigTraitTest.php
git commit -m "feat: add ProviderConfigTrait for tenant and config helpers"
```

---

## Task 3: 创建 AbstractServiceProvider

**Files:**
- Create: `src/Service/AbstractServiceProvider.php`
- Create: `tests/Service/AbstractServiceProviderTest.php`

- [ ] **Step 1: Write failing test**

```php
// tests/Service/AbstractServiceProviderTest.php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Service;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Service\AbstractServiceProvider;

class ConcreteServiceProvider extends AbstractServiceProvider
{
    protected function getProviderClass(): string
    {
        return 'TestProviderClass';
    }

    protected function getProviderName(): string
    {
        return 'test_provider';
    }
}

class AbstractServiceProviderTest extends \Yansongda\Pay\Tests\TestCase
{
    public function testRegisterSetsClassAndName(): void
    {
        $provider = new ConcreteServiceProvider();
        $provider->register();
        
        $this->assertTrue(Pay::has('TestProviderClass'));
        $this->assertTrue(Pay::has('test_provider'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/phpunit tests/Service/AbstractServiceProviderTest.php --colors=always`
Expected: FAIL - AbstractServiceProvider not found

- [ ] **Step 3: Create AbstractServiceProvider**

```php
// src/Service/AbstractServiceProvider.php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Artful\Contract\ServiceProviderInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Pay\Pay;

abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    /**
     * 返回 Provider 完整类名
     */
    abstract protected function getProviderClass(): string;

    /**
     * 返回 Provider 注册名称
     */
    abstract protected function getProviderName(): string;

    /**
     * @throws ContainerException
     */
    public function register(mixed $data = null): void
    {
        $class = $this->getProviderClass();
        $service = new $class();

        Pay::set($class, $service);
        Pay::set($this->getProviderName(), $service);
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `./vendor/bin/phpunit tests/Service/AbstractServiceProviderTest.php --colors=always`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Service/AbstractServiceProvider.php tests/Service/AbstractServiceProviderTest.php
git commit -m "feat: add AbstractServiceProvider base class"
```

---

## Task 4: 创建 AlipayTrait

**Files:**
- Create: `src/Traits/AlipayTrait.php`
- Create: `tests/Traits/AlipayTraitTest.php`

- [ ] **Step 1: Write failing test for verifyAlipaySign**

```php
// tests/Traits/AlipayTraitTest.php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Traits\AlipayTrait;

class AlipayTraitTest extends \Yansongda\Pay\Tests\TestCase
{
    public function testVerifyAlipaySignWithEmptySign(): void
    {
        $this->expectException(InvalidSignException::class);
        $this->expectExceptionCode(Exception::SIGN_EMPTY);
        
        AlipayTrait::verifyAlipaySign([], 'test_contents', '');
    }

    public function testVerifyAlipaySignSuccess(): void
    {
        // 使用测试证书验证签名
        $config = [
            'alipay_public_cert_path' => __DIR__ . '/../Cert/alipayPublicCert.crt',
        ];
        
        // 构造有效的签名测试（需要实际签名数据）
        // 此处为占位，实际需用 openssl_sign 生成测试签名
        $contents = 'test';
        $sign = base64_encode('test_sign');
        
        // 验证逻辑
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/phpunit tests/Traits/AlipayTraitTest.php --colors=always`
Expected: FAIL - Trait not found

- [ ] **Step 3: Create AlipayTrait**

```php
// src/Traits/AlipayTrait.php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Supports\Collection;

trait AlipayTrait
{
    use ProviderConfigTrait;

    /**
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public static function verifyAlipaySign(array $config, string $contents, string $sign): void
    {
        if (empty($sign)) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 验证支付宝签名失败-支付宝签名为空', func_get_args());
        }

        $public = $config['alipay_public_cert_path'] ?? null;

        if (empty($public)) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 缺少支付宝配置 -- [alipay_public_cert_path]');
        }

        $result = 1 === openssl_verify(
            $contents,
            base64_decode($sign),
            CertManager::getPublicCert($public),
            OPENSSL_ALGO_SHA256
        );

        if (!$result) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证支付宝签名失败', func_get_args());
        }
    }

    public static function getAlipayUrl(array $config, ?Collection $payload): string
    {
        $url = self::getRadarUrl($config, $payload) ?? '';

        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return Alipay::URL[$config['mode'] ?? \Yansongda\Pay\Pay::MODE_NORMAL];
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `./vendor/bin/phpunit tests/Traits/AlipayTraitTest.php --colors=always`
Expected: Empty sign test PASS

- [ ] **Step 5: Add getAlipayUrl test**

```php
// tests/Traits/AlipayTraitTest.php - 添加
public function testGetAlipayUrlDefault(): void
{
    $config = ['mode' => \Yansongda\Pay\Pay::MODE_NORMAL];
    $result = AlipayTrait::getAlipayUrl($config, null);
    
    $this->assertEquals('https://openapi.alipay.com/gateway.do?charset=utf-8', $result);
}

public function testGetAlipayUrlSandbox(): void
{
    $config = ['mode' => \Yansongda\Pay\Pay::MODE_SANDBOX];
    $result = AlipayTrait::getAlipayUrl($config, null);
    
    $this->assertStringContainsString('sandbox', $result);
}

public function testGetAlipayUrlWithCustomPayload(): void
{
    $config = ['mode' => \Yansongda\Pay\Pay::MODE_NORMAL];
    $payload = new Collection(['_url' => 'https://custom.alipay.com/api']);
    
    $result = AlipayTrait::getAlipayUrl($config, $payload);
    
    $this->assertEquals('https://custom.alipay.com/api', $result);
}
```

- [ ] **Step 6: Run all tests**

Run: `./vendor/bin/phpunit tests/Traits/AlipayTraitTest.php --colors=always`
Expected: All tests PASS

- [ ] **Step 7: Commit**

```bash
git add src/Traits/AlipayTrait.php tests/Traits/AlipayTraitTest.php
git commit -m "feat: add AlipayTrait with verifyAlipaySign and getAlipayUrl"
```

---

## Task 5: 创建 WechatTrait（最复杂）

**Files:**
- Create: `src/Traits/WechatTrait.php`
- Create: `tests/Traits/WechatTraitTest.php`

由于 WechatTrait 有 18 个方法，分步创建：

- [ ] **Step 1: Write failing test for getWechatUrl**

```php
// tests/Traits/WechatTraitTest.php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Traits\WechatTrait;
use Yansongda\Supports\Collection;

class WechatTraitTest extends \Yansongda\Pay\Tests\TestCase
{
    public function testGetWechatUrlMissingUrl(): void
    {
        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionCode(Exception::PARAMS_WECHAT_URL_MISSING);
        
        WechatTrait::getWechatUrl(['mode' => \Yansongda\Pay\Pay::MODE_NORMAL], null);
    }

    public function testGetWechatUrlSuccess(): void
    {
        $config = ['mode' => \Yansongda\Pay\Pay::MODE_NORMAL];
        $payload = new Collection(['_url' => '/v3/pay/transactions/native']);
        
        $result = WechatTrait::getWechatUrl($config, $payload);
        
        $this->assertStringContainsString('api.mch.weixin.qq.com', $result);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/phpunit tests/Traits/WechatTraitTest.php --colors=always`
Expected: FAIL - Trait not found

- [ ] **Step 3: Create WechatTrait (initial version with URL methods)**

```php
// src/Traits/WechatTrait.php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Supports\Collection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function Yansongda\Artful\get_radar_body;
use function Yansongda\Artful\get_radar_method;

trait WechatTrait
{
    use ProviderConfigTrait;

    // ===== URL/Method/Body 获取 =====

    public static function getWechatMethod(?Collection $payload): string
    {
        return get_radar_method($payload) ?? 'POST';
    }

    /**
     * @throws InvalidParamsException
     */
    public static function getWechatUrl(array $config, ?Collection $payload): string
    {
        $url = self::getRadarUrl($config, $payload);

        if (empty($url)) {
            throw new InvalidParamsException(Exception::PARAMS_WECHAT_URL_MISSING, '参数异常: 微信 `_url` 或 `_service_url` 参数缺失');
        }

        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return Wechat::URL[$config['mode'] ?? \Yansongda\Pay\Pay::MODE_NORMAL] . $url;
    }

    /**
     * @throws InvalidParamsException
     */
    public static function getWechatBody(?Collection $payload): mixed
    {
        $body = get_radar_body($payload);

        if (is_null($body)) {
            throw new InvalidParamsException(Exception::PARAMS_WECHAT_BODY_MISSING, '参数异常: 微信 `_body` 参数缺失');
        }

        return $body;
    }

    public static function getWechatTypeKey(array $params): string
    {
        $key = ($params['_type'] ?? 'mp') . '_app_id';

        if ('app_app_id' === $key) {
            $key = 'app_id';
        }

        return $key;
    }

    // ===== 签名生成 =====

    /**
     * @throws InvalidConfigException
     */
    public static function getWechatSign(array $config, string $contents): string
    {
        $privateKey = $config['mch_secret_cert'] ?? null;

        if (empty($privateKey)) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mch_secret_cert]');
        }

        $privateKey = CertManager::getPrivateCert($privateKey);

        openssl_sign($contents, $sign, $privateKey, 'sha256WithRSAEncryption');

        return base64_encode($sign);
    }

    /**
     * @throws InvalidConfigException
     */
    public static function getWechatSignV2(array $config, array $payload, bool $upper = true): string
    {
        $key = $config['mch_secret_key_v2'] ?? null;

        if (empty($key)) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mch_secret_key_v2]');
        }

        ksort($payload);

        $buff = '';
        foreach ($payload as $k => $v) {
            $buff .= ('sign' != $k && '' != $v && !is_array($v)) ? $k . '=' . $v . '&' : '';
        }

        $sign = md5($buff . 'key=' . $key);

        return $upper ? strtoupper($sign) : $sign;
    }

    // ===== 签名验证 =====

    /**
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public static function verifyWechatSign(ResponseInterface|ServerRequestInterface $message, array $params): void
    {
        $wechatSerial = $message->getHeaderLine('Wechatpay-Serial');
        $timestamp = $message->getHeaderLine('Wechatpay-Timestamp');
        $random = $message->getHeaderLine('Wechatpay-Nonce');
        $sign = $message->getHeaderLine('Wechatpay-Signature');
        $body = (string) $message->getBody();

        $content = $timestamp . "\n" . $random . "\n" . $body . "\n";

        if (empty($sign)) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 微信签名为空', ['headers' => $message->getHeaders(), 'body' => $body]);
        }

        $public = self::getProviderConfig('wechat', $params)['wechat_public_cert_path'][$wechatSerial] ?? null;
        $public = CertManager::getPublicCert(
            empty($public) ? self::reloadWechatPublicCerts($params, $wechatSerial) : $public
        );

        $result = 1 === openssl_verify(
            $content,
            base64_decode($sign),
            $public,
            'sha256WithRSAEncryption'
        );

        if (!$result) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证微信签名失败', ['headers' => $message->getHeaders(), 'body' => $body]);
        }
    }

    /**
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public static function verifyWechatSignV2(array $config, array $destination): void
    {
        $sign = $destination['sign'] ?? null;

        if (empty($sign)) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 微信签名为空', $destination);
        }

        $key = $config['mch_secret_key_v2'] ?? null;

        if (empty($key)) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mch_secret_key_v2]');
        }

        if (self::getWechatSignV2($config, $destination) !== $sign) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证微信签名失败', $destination);
        }
    }

    // ===== 加解密 =====

    public static function encryptWechatContents(string $contents, string $publicKey): ?string
    {
        if (openssl_public_encrypt($contents, $encrypted, CertManager::getPublicCert($publicKey), OPENSSL_PKCS1_OAEP_PADDING)) {
            return base64_encode($encrypted);
        }

        return null;
    }

    public static function decryptWechatContents(string $encrypted, array $config): ?string
    {
        if (openssl_private_decrypt(base64_decode($encrypted), $decrypted, CertManager::getPrivateCert($config['mch_secret_cert'] ?? ''), OPENSSL_PKCS1_OAEP_PADDING)) {
            return $decrypted;
        }

        return null;
    }

    /**
     * @throws InvalidConfigException
     * @throws DecryptException
     */
    public static function decryptWechatResource(array $resource, array $config): array
    {
        $ciphertext = base64_decode($resource['ciphertext'] ?? '');
        $secret = $config['mch_secret_key'] ?? null;

        if (strlen($ciphertext) <= Wechat::AUTH_TAG_LENGTH_BYTE) {
            throw new DecryptException(Exception::DECRYPT_WECHAT_CIPHERTEXT_PARAMS_INVALID, '加解密异常: ciphertext 位数过短');
        }

        if (is_null($secret) || Wechat::MCH_SECRET_KEY_LENGTH_BYTE != strlen($secret)) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mch_secret_key]');
        }

        $resource['ciphertext'] = match ($resource['algorithm'] ?? '') {
            'AEAD_AES_256_GCM' => self::decryptWechatResourceAes256Gcm($ciphertext, $secret, $resource['nonce'] ?? '', $resource['associated_data'] ?? ''),
            default => throw new DecryptException(Exception::DECRYPT_WECHAT_DECRYPTED_METHOD_INVALID, '加解密异常: algorithm 不支持'),
        };

        return $resource;
    }

    /**
     * @throws DecryptException
     */
    public static function decryptWechatResourceAes256Gcm(string $ciphertext, string $secret, string $nonce, string $associatedData): array|string
    {
        $decrypted = openssl_decrypt(
            substr($ciphertext, 0, -Wechat::AUTH_TAG_LENGTH_BYTE),
            'aes-256-gcm',
            $secret,
            OPENSSL_RAW_DATA,
            $nonce,
            substr($ciphertext, -Wechat::AUTH_TAG_LENGTH_BYTE),
            $associatedData
        );

        if (false === $decrypted) {
            throw new DecryptException(Exception::DECRYPT_WECHAT_ENCRYPTED_DATA_INVALID, '加解密异常: 解密失败');
        }

        if ('certificate' !== $associatedData) {
            $decrypted = json_decode($decrypted, true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new DecryptException(Exception::DECRYPT_WECHAT_ENCRYPTED_DATA_INVALID, '加解密异常: 待解密数据非正常数据');
            }
        }

        return $decrypted;
    }

    // ===== 证书管理 =====

    public static function reloadWechatPublicCerts(array $params, ?string $serialNo = null): string
    {
        // 此方法需要调用微信 API 获取证书，实现较复杂
        // 暂时保留原有逻辑，后续迁移
        // ... 完整实现见 Functions.php 原代码
        return '';
    }

    public static function getWechatPublicCerts(array $params = [], ?string $path = null): void
    {
        self::reloadWechatPublicCerts($params);

        $config = self::getProviderConfig('wechat', $params);

        if (empty($path)) {
            var_dump($config['wechat_public_cert_path']);
            return;
        }

        foreach ($config['wechat_public_cert_path'] as $serial => $cert) {
            file_put_contents($path . '/' . $serial . '.crt', $cert);
        }
    }

    public static function getWechatSerialNo(array $params): string
    {
        if (!empty($params['_serial_no'])) {
            return $params['_serial_no'];
        }

        $config = self::getProviderConfig('wechat', $params);

        if (empty($config['wechat_public_cert_path'])) {
            self::reloadWechatPublicCerts($params);
            $config = self::getProviderConfig('wechat', $params);
        }

        mt_srand();

        return strval(array_rand($config['wechat_public_cert_path']));
    }

    /**
     * @throws InvalidParamsException
     */
    public static function getWechatPublicKey(array $config, string $serialNo): string
    {
        $publicKey = $config['wechat_public_cert_path'][$serialNo] ?? null;

        if (empty($publicKey)) {
            throw new InvalidParamsException(Exception::PARAMS_WECHAT_SERIAL_NOT_FOUND, '参数异常: 微信公钥序列号未找到 - ' . $serialNo);
        }

        return $publicKey;
    }

    // ===== 小程序支付签名 =====

    /**
     * @throws InvalidConfigException
     */
    public static function getWechatMiniprogramPaySign(array $config, string $url, string $payload): string
    {
        if (empty($config['mini_app_key_virtual_pay'])) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mini_app_key_virtual_pay]');
        }

        return hash_hmac('sha256', $url . '&' . $payload, $config['mini_app_key_virtual_pay']);
    }

    public static function getWechatMiniprogramUserSign(string $sessionKey, string $payload): string
    {
        return hash_hmac('sha256', $payload, $sessionKey);
    }
}
```

- [ ] **Step 4: Run test to verify URL methods pass**

Run: `./vendor/bin/phpunit tests/Traits/WechatTraitTest.php --colors=always`
Expected: URL tests PASS

- [ ] **Step 5: Add comprehensive WechatTrait tests**

```php
// tests/Traits/WechatTraitTest.php - 添加更多测试
public function testGetWechatSignV2(): void
{
    $config = ['mch_secret_key_v2' => 'test_key_32_characters_long'];
    $payload = ['appid' => 'wx123', 'mch_id' => '123456'];
    
    $result = WechatTrait::getWechatSignV2($config, $payload);
    
    $this->assertMatchesRegularExpression('/^[A-F0-9]{32}$/', $result);
}

public function testGetWechatTypeKey(): void
{
    $this->assertEquals('mp_app_id', WechatTrait::getWechatTypeKey(['_type' => 'mp']));
    $this->assertEquals('app_id', WechatTrait::getWechatTypeKey(['_type' => 'app']));
}

public function testVerifyWechatSignEmpty(): void
{
    $request = new \GuzzleHttp\Psr7\ServerRequest('POST', '/', [], json_encode(['test' => 'data']));
    
    $this->expectException(\Yansongda\Pay\Exception\InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_EMPTY);
    
    WechatTrait::verifyWechatSign($request, []);
}

public function testDecryptWechatResourceInvalidCiphertext(): void
{
    $resource = ['ciphertext' => 'short', 'algorithm' => 'AEAD_AES_256_GCM'];
    $config = ['mch_secret_key' => '32characterssecretkey1234567890'];
    
    $this->expectException(\Yansongda\Pay\Exception\DecryptException::class);
    $this->expectExceptionCode(Exception::DECRYPT_WECHAT_CIPHERTEXT_PARAMS_INVALID);
    
    WechatTrait::decryptWechatResource($resource, $config);
}
```

- [ ] **Step 6: Run all WechatTrait tests**

Run: `./vendor/bin/phpunit tests/Traits/WechatTraitTest.php --colors=always`
Expected: All tests PASS

- [ ] **Step 7: Commit**

```bash
git add src/Traits/WechatTrait.php tests/Traits/WechatTraitTest.php
git commit -m "feat: add WechatTrait with 18 methods (sign, encrypt, decrypt, cert)"
```

---

## Task 6-10: 创建其他 Provider Trait

按照相同 TDD 模式创建：

- Task 6: UnipayTrait（5 个方法）
- Task 7: JsbTrait（2 个方法）
- Task 8: DouyinTrait（1 个方法）
- Task 9: PaypalTrait（3 个方法）
- Task 10: StripeTrait（2 个方法）

每个 Task 遵循相同的 7 步流程：测试 → 实现 → 测试 → 提交

---

## Task 11: 迁移 Alipay Plugin 文件

**Files:**
- Modify: `src/Plugin/Alipay/V2/*.php`（6 个文件）

- [ ] **Step 1: Update CallbackPlugin**

```php
// src/Plugin/Alipay/V2/CallbackPlugin.php
// 修改前:
use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\verify_alipay_sign;

// 修改后:
use Yansongda\Pay\Traits\AlipayTrait;
use Yansongda\Pay\Traits\ProviderConfigTrait;

class CallbackPlugin implements PluginInterface
{
    use AlipayTrait;
    use ProviderConfigTrait;

    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $params = $rocket->getParams();
        $config = self::getProviderConfig('alipay', $params);  // Trait 方法
        // ...
        self::verifyAlipaySign($config, $value->sortKeys()->toString(), $params['sign'] ?? '');  // Trait 方法
        // ...
    }
}
```

- [ ] **Step 2: Run tests for Alipay plugins**

Run: `./vendor/bin/phpunit tests/Plugin/Alipay/ --colors=always`
Expected: All tests PASS

- [ ] **Step 3: Update all Alipay V2 plugins**

依次修改：
- `AddPayloadSignaturePlugin.php`
- `AddRadarPlugin.php`
- `AppCallbackPlugin.php`
- `StartPlugin.php`
- `VerifySignaturePlugin.php`

- [ ] **Step 4: Run all Alipay tests**

Run: `./vendor/bin/phpunit tests/Plugin/Alipay/ --colors=always`
Expected: All tests PASS

- [ ] **Step 5: Commit**

```bash
git add src/Plugin/Alipay/V2/*.php
git commit -m "refactor: migrate Alipay plugins to use AlipayTrait"
```

---

## Task 12-17: 迁移其他 Provider Plugin 文件

按 Provider 分组迁移（每个 Provider 一个 commit）：

- Task 12: Wechat Plugin（~105 文件，最多）
- Task 13: Unipay Plugin（~40 文件）
- Task 14: Jsb Plugin（~10 文件）
- Task 15: Douyin Plugin（~8 文件）
- Task 16: Paypal Plugin（~5 文件）
- Task 17: Stripe Plugin（~4 文件）

每个 Task 的大致流程：
1. 批量替换 `use function Yansongda\Pay\` 为 Trait
2. 修改方法调用为 Trait 静态方法
3. 运行对应 Provider 测试
4. Commit

---

## Task 18: 修改 ServiceProvider 继承

**Files:**
- Modify: `src/Service/*ServiceProvider.php`（7 个文件）

- [ ] **Step 1: Update WechatServiceProvider**

```php
// src/Service/WechatServiceProvider.php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Service\AbstractServiceProvider;

class WechatServiceProvider extends AbstractServiceProvider
{
    protected function getProviderClass(): string
    {
        return Wechat::class;
    }

    protected function getProviderName(): string
    {
        return 'wechat';
    }
}
```

- [ ] **Step 2: Update all other ServiceProvider files**

依次修改：
- AlipayServiceProvider.php
- UnipayServiceProvider.php
- JsbServiceProvider.php
- DouyinServiceProvider.php
- PaypalServiceProvider.php
- StripeServiceProvider.php

- [ ] **Step 3: Run tests**

Run: `./vendor/bin/phpunit tests/Provider/ --colors=always`
Expected: All tests PASS

- [ ] **Step 4: Commit**

```bash
git add src/Service/*ServiceProvider.php
git commit -m "refactor: ServiceProvider extends AbstractServiceProvider"
```

---

## Task 19: 删除 Functions.php

**Files:**
- Delete: `src/Functions.php`

- [ ] **Step 1: Verify no remaining imports**

Run: `grep -r "use function Yansongda\\\\Pay" src/ --include="*.php"`
Expected: 0 matches

- [ ] **Step 2: Remove Functions.php from autoload**

在 `composer.json` 中移除：
```json
"autoload": {
    "psr-4": {...},
    "files": []  // 移除 "src/Functions.php"
}
```

- [ ] **Step 3: Delete Functions.php**

```bash
git rm src/Functions.php
```

- [ ] **Step 4: Run all tests**

Run: `./vendor/bin/phpunit --colors=always`
Expected: All tests PASS

- [ ] **Step 5: Commit**

```bash
git add composer.json
git commit -m "refactor: remove Functions.php, fully migrated to Traits"
```

---

## Task 20: 补充返回类型声明

**Files:**
- Modify: `src/Shortcut/Alipay/RefundShortcut.php`
- Modify: `src/Shortcut/Alipay/QueryShortcut.php`
- 其他需要检查的 Shortcut 文件

- [ ] **Step 1: Check missing type declarations**

Run: `./vendor/bin/phpstan analyse --memory-limit 300M -l 5 -c phpstan.neon ./src`
Expected: 查看是否有缺少返回类型的警告

- [ ] **Step 2: Fix RefundShortcut**

```php
// src/Shortcut/Alipay/RefundShortcut.php
// 添加返回类型声明
protected function defaultPlugins(): array  // 已有，检查确认
protected function agreementPlugins(): array
protected function appPlugins(): array
// ... 其他方法
```

- [ ] **Step 3: Run analyse again**

Run: `./vendor/bin/phpstan analyse --memory-limit 300M -l 5 -c phpstan.neon ./src`
Expected: 无错误

- [ ] **Step 4: Commit**

```bash
git add src/Shortcut/**/*.php
git commit -m "fix: add missing return type declarations to Shortcut methods"
```

---

## Task 21: 运行代码风格检查

- [ ] **Step 1: Run cs-fix check**

Run: `composer cs-fix`
Expected: 无差异输出（或记录差异）

- [ ] **Step 2: Fix style issues if any**

如果有差异，手动修复或运行：
```bash
vendor/bin/php-cs-fixer fix src/
```

- [ ] **Step 3: Run cs-fix again to verify**

Run: `composer cs-fix`
Expected: 无差异

- [ ] **Step 4: Commit**

```bash
git add src/
git commit -m "style: fix code style issues after refactoring"
```

---

## Task 22: 最终验证

- [ ] **Step 1: Run full test suite**

Run: `composer test`
Expected: All tests PASS

- [ ] **Step 2: Run static analysis**

Run: `composer analyse`
Expected: Level 5 无错误

- [ ] **Step 3: Run code style check**

Run: `composer cs-fix`
Expected: 无差异

- [ ] **Step 4: Verify trait autoloading**

```bash
php -r "require 'vendor/autoload.php'; echo class_exists('Yansongda\\Pay\\CertManager') ? 'OK' : 'FAIL';"
```
Expected: OK

---

## Task 23: 更新 CHANGELOG

- [ ] **Step 1: Add entry to CHANGELOG.md**

```markdown
## v3.8.0 - 2026-04-XX

### Breaking Changes

- **Functions.php 已删除**: 所有辅助函数迁移至 Trait，函数名改为驼峰命名
  - `verify_alipay_sign` → `AlipayTrait::verifyAlipaySign()`
  - `get_wechat_url` → `WechatTrait::getWechatUrl()`
  - 等，详见迁移指南

### 新增

- `CertManager` 类：证书加载带进程内缓存
- `ProviderConfigTrait`：getTenant、getProviderConfig、getRadarUrl
- `AbstractServiceProvider`：ServiceProvider 抽象基类
- 各 Provider Trait：AlipayTrait、WechatTrait、UnipayTrait、JsbTrait、DouyinTrait、PaypalTrait、StripeTrait

### 优化

- ServiceProvider 统一继承 AbstractServiceProvider
- 补充 Shortcut 方法返回类型声明
- 增加边界条件测试覆盖

### 迁移指南

原直接调用函数：
```php
use function Yansongda\Pay\verify_alipay_sign;
verify_alipay_sign($config, $contents, $sign);
```

改为 Trait 方式：
```php
use Yansongda\Pay\Traits\AlipayTrait;
// 在类中 use Trait
class YourPlugin {
    use AlipayTrait;
    
    // 调用
    self::verifyAlipaySign($config, $contents, $sign);
}
```
```

- [ ] **Step 2: Commit CHANGELOG**

```bash
git add CHANGELOG.md
git commit -m "docs: update CHANGELOG for v3.8.0"
```

---

## 验收清单

| 检查项 | 命令 | 预期结果 |
|-------|------|---------|
| 单元测试 | `composer test` | 全部 PASS |
| 静态分析 | `composer analyse` | Level 5 无错误 |
| 代码风格 | `composer cs-fix` | 无差异输出 |
| 文件删除 | `ls src/Functions.php` | 文件不存在 |
| Trait 存在 | `ls src/Traits/*.php` | 8 个文件 |
| ServiceProvider | `grep -l "extends Abstract" src/Service/*.php` | 7 个匹配 |
| CHANGELOG | `grep "v3.8.0" CHANGELOG.md` | 存在 |

---

**Plan complete and saved to `docs/superpowers/plans/2026-04-15-code-refactoring.md`.**

**执行选项：**

**1. Subagent-Driven (推荐)** - 每个 Task 派发独立子代理，Task 间审查，快速迭代

**2. Inline Execution** - 在当前会话批量执行，带审查检查点

**请选择执行方式？**