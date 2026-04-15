# yansongda/pay 代码重构设计文档

**日期**: 2026-04-15
**版本**: v3.8.0（下一个 minor 版本）
**影响范围**: Functions.php、ServiceProvider、证书缓存、类型声明、测试覆盖

---

## 一、概述

本次重构针对 yansongda/pay SDK 的核心代码结构进行优化，涉及以下方面：

1. **Functions.php 拆分** - 829 行辅助函数文件拆分为按 Provider 组织的 Trait
2. **ServiceProvider 抽象** - 7 个重复的 ServiceProvider 统一为抽象基类
3. **证书缓存机制** - 证书文件读取添加进程内缓存
4. **类型声明补充** - Shortcut 方法添加返回类型声明
5. **测试覆盖增强** - 添加签名验证失败、解密失败等边界条件测试

**不处理项**：
- HTTP 连接复用（保持现状，不在 SDK 层面处理）
- 目录结构统一（暂时忽略）
- 日志脱敏、证书权限检查等其他安全项（暂不处理）

---

## 二、Functions.php 拆分方案

### 2.1 当前状态

`src/Functions.php` 包含 829 行代码，包含以下函数分类：

| 类别 | 函数数量 | 说明 |
|------|---------|------|
| 签名验证 | 8 | `verify_*_sign` 系列 |
| URL 构建 | 7 | `get_*_url` 系列 |
| 加解密 | 5 | 微信资源加解密 |
| 证书处理 | 2 | `get_public_cert`、`get_private_cert` |
| 配置获取 | 3 | `get_tenant`、`get_provider_config`、`get_radar_url` |
| 其他 | 多个 | 微信支付签名、序列号获取等 |

### 2.2 目标结构

```
src/Traits/
├── AlipayTrait.php          # 支付宝相关函数
├── WechatTrait.php          # 微信相关函数（最多）
├── UnipayTrait.php          # 银联相关函数
├── JsbTrait.php             # 江苏银行相关函数
├── DouyinTrait.php          # 抖音相关函数
├── PaypalTrait.php          # PayPal 相关函数
├── StripeTrait.php          # Stripe 相关函数
├── ProviderConfigTrait.php  # 配置获取（共享）

src/CertManager.php          # 证书缓存（独立类，共享）
```

### 2.3 各 Trait 内容分配

#### AlipayTrait

```php
namespace Yansongda\Pay\Traits;

use Yansongda\Pay\CertManager;

trait AlipayTrait
{
    /**
     * 验证支付宝签名
     */
    public static function verifyAlipaySign(array $config, string $contents, string $sign): void
    {
        // ...
        $public = CertManager::getPublicCert($config['alipay_public_cert_path']);
        // ...
    }
    
    /**
     * 获取支付宝 API URL
     */
    public static function getAlipayUrl(array $config, ?Collection $payload): string;
}
```

#### WechatTrait（最复杂）

```php
namespace Yansongda\Pay\Traits;

use Yansongda\Pay\CertManager;

trait WechatTrait
{
    // 签名验证（2个）
    public static function verifyWechatSign(ResponseInterface|ServerRequestInterface $message, array $params): void;
    public static function verifyWechatSignV2(array $config, array $destination): void;
    
    // URL/Body 获取（3个）
    public static function getWechatMethod(?Collection $payload): string;
    public static function getWechatUrl(array $config, ?Collection $payload): string;
    public static function getWechatBody(?Collection $payload): mixed;
    
    // 签名生成（3个）
    public static function getWechatSign(array $config, string $contents): string
    {
        $privateKey = CertManager::getPrivateCert($config['mch_secret_cert']);
        // ...
    }
    public static function getWechatSignV2(array $config, array $payload, bool $upper = true): string;
    public static function getWechatMiniprogramPaySign(array $config, string $url, string $payload): string;
    
    // 加解密（4个）
    public static function encryptWechatContents(string $contents, string $publicKey): ?string
    {
        $publicKey = CertManager::getPublicCert($publicKey);
        // ...
    }
    public static function decryptWechatContents(string $encrypted, array $config): ?string
    {
        $privateKey = CertManager::getPrivateCert($config['mch_secret_cert']);
        // ...
    }
    public static function decryptWechatResource(array $resource, array $config): array;
    public static function decryptWechatResourceAes256Gcm(...): array|string;
    
    // 证书管理（4个）
    public static function reloadWechatPublicCerts(array $params, ?string $serialNo = null): string;
    public static function getWechatPublicCerts(array $params = [], ?string $path = null): void;
    public static function getWechatSerialNo(array $params): string;
    public static function getWechatPublicKey(array $config, string $serialNo): string;
    
    // 其他（2个）
    public static function getWechatTypeKey(array $params): string;
    public static function getWechatMiniprogramUserSign(string $sessionKey, string $payload): string;
}
```

#### UnipayTrait

```php
namespace Yansongda\Pay\Traits;

use Yansongda\Pay\CertManager;

trait UnipayTrait
{
    public static function verifyUnipaySign(array $config, string $contents, string $sign, ?string $signPublicKeyCert = null): void
    {
        $public = CertManager::getPublicCert($signPublicKeyCert ?? $config['unipay_public_cert_path']);
        // ...
    }
    public static function verifyUnipaySignQra(array $config, array $destination): void;
    public static function getUnipayUrl(array $config, ?Collection $payload): string;
    public static function getUnipayBody(?Collection $payload): string;
    public static function getUnipaySignQra(array $config, array $payload): string;
}
```

#### JsbTrait

```php
namespace Yansongda\Pay\Traits;

use Yansongda\Pay\CertManager;

trait JsbTrait
{
    public static function verifyJsbSign(array $config, string $content, string $sign): void
    {
        $publicCert = CertManager::getPublicCert($config['jsb_public_cert_path']);
        // ...
    }
    public static function getJsbUrl(array $config, ?Collection $payload): string;
}
```

#### DouyinTrait

```php
trait DouyinTrait
{
    public static function getDouyinUrl(array $config, ?Collection $payload): string;
}
```

#### PaypalTrait

```php
trait PaypalTrait
{
    public static function verifyPaypalWebhookSign(ServerRequestInterface $request, array $params): void;
    public static function getPaypalUrl(array $config, ?Collection $payload): string;
    public static function getPaypalAccessToken(array $params): string;
}
```

#### StripeTrait

```php
trait StripeTrait
{
    public static function verifyStripeWebhookSign(ServerRequestInterface $request, array $params): void;
    public static function getStripeUrl(array $config, ?Collection $payload): string;
}
```

#### CertManager（独立类 - 证书缓存）

```php
namespace Yansongda\Pay;

class CertManager
{
    private static array $cache = [];
    
    /**
     * 获取公钥证书内容（带进程内缓存）
     */
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
    
    /**
     * 获取私钥证书内容（带进程内缓存）
     */
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
    
    /**
     * 清除证书缓存（用于测试）
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
}
```

#### ProviderConfigTrait（共享）

```php
namespace Yansongda\Pay\Traits;

trait ProviderConfigTrait
{
    /**
     * 获取租户标识
     */
    public static function getTenant(array $params = []): string
    {
        return strval($params['_config'] ?? 'default');
    }
    
    /**
     * 获取 Provider 配置
     */
    public static function getProviderConfig(string $provider, array $params = []): array
    {
        $config = Pay::get(ConfigInterface::class);
        return $config->get($provider, [])[self::getTenant($params)] ?? [];
    }
    
    /**
     * 获取 Radar URL
     */
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

### 2.4 函数命名变更

Trait 中方法名改为 PSR 风格（驼峰命名）：

| 原函数名 | 新方法名 |
|---------|---------|
| `verify_alipay_sign` | `verifyAlipaySign` |
| `get_alipay_url` | `getAlipayUrl` |
| `verify_wechat_sign` | `verifyWechatSign` |
| `decrypt_wechat_resource` | `decryptWechatResource` |
| ... | ... |

### 2.5 调用方迁移

所有使用 Functions.php 的代码改为使用 Trait：

**Plugin 文件迁移示例**：

```php
// 修改前
use function Yansongda\Pay\verify_alipay_sign;
use function Yansongda\Pay\get_provider_config;

// 修改后
use Yansongda\Pay\Traits\AlipayTrait;
use Yansongda\Pay\Traits\ProviderConfigTrait;

class CallbackPlugin implements PluginInterface
{
    use AlipayTrait;
    use ProviderConfigTrait;
    
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $config = self::getProviderConfig('alipay', $params);
        self::verifyAlipaySign($config, $contents, $sign);
        // ...
    }
}
```

### 2.6 文件删除

完成迁移后，**直接删除** `src/Functions.php`。

---

## 三、ServiceProvider 抽象基类

### 3.1 当前状态

7 个 ServiceProvider 文件结构完全相同（20-24 行）：

```php
class WechatServiceProvider implements ServiceProviderInterface
{
    public function register(mixed $data = null): void
    {
        $service = new Wechat();
        Pay::set(Wechat::class, $service);
        Pay::set('wechat', $service);
    }
}
```

### 3.2 抽象基类设计

```php
// src/Service/AbstractServiceProvider.php
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
     * 返回 Provider 注册名称（如 'wechat'）
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

### 3.3 子类实现

所有 ServiceProvider 改为继承基类：

```php
// src/Service/WechatServiceProvider.php
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

同理修改：
- AlipayServiceProvider
- UnipayServiceProvider
- JsbServiceProvider
- DouyinServiceProvider
- PaypalServiceProvider
- StripeServiceProvider

---

## 四、类型声明补充

### 4.1 受影响文件

根据探索结果，以下文件的方法缺少返回类型声明：

| 文件 | 缺少类型声明的方法数 |
|-----|-------------------|
| `src/Shortcut/Alipay/RefundShortcut.php` | 10 |
| `src/Shortcut/Alipay/QueryShortcut.php` | 9 |
| 其他 Shortcut 文件 | 需逐一检查 |

### 4.2 修改示例

```php
// 修改前
protected function defaultPlugins(): array
{
    return [...];
}

// 修改后 - 添加 : array（但实际已有，需要验证）
protected function defaultPlugins(): array
{
    return [...];
}
```

### 4.3 检查范围

使用 PHPStan Level 5 验证所有类型声明完整性。

---

## 五、测试覆盖增强

### 5.1 新增测试文件

```
tests/Traits/
├── AlipayTraitTest.php          (新)
├── WechatTraitTest.php          (新)
├── UnipayTraitTest.php          (新)
├── JsbTraitTest.php             (新)
├── DouyinTraitTest.php          (新)
├── PaypalTraitTest.php          (新)
├── StripeTraitTest.php          (新)
├── ProviderConfigTraitTest.php  (新)

tests/CertManagerTest.php        (新 - 证书缓存类)
```

### 5.2 边界条件测试

#### CallbackPlugin 测试增强

**tests/Plugin/Alipay/V2/CallbackPluginTest.php**：

```php
public function testVerifySignWithEmptySign(): void
{
    $params = ['out_trade_no' => 'test', 'sign' => '']; // 空签名
    
    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_EMPTY);
    
    Pay::alipay()->callback($params);
}

public function testVerifySignWithInvalidSign(): void
{
    $params = ['out_trade_no' => 'test', 'sign' => 'invalid_sign_string'];
    
    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);
    
    Pay::alipay()->callback($params);
}

public function testVerifySignWithMissingCertConfig(): void
{
    // 配置中缺少 alipay_public_cert_path
    Pay::config(['alipay' => ['default' => ['app_id' => 'test']]]);
    
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_ALIPAY_INVALID);
    
    Pay::alipay()->callback(['sign' => 'test']);
}
```

**tests/Plugin/Wechat/V3/CallbackPluginTest.php**：

```php
public function testVerifySignWithEmptySignatureHeader(): void
{
    $request = new ServerRequest('POST', '/', [], json_encode(['test' => 'data']));
    // 缺少 Wechatpay-Signature header
    
    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_EMPTY);
    
    Pay::wechat()->callback($request);
}

public function testDecryptResourceWithInvalidCiphertext(): void
{
    // ciphertext 长度不足
    $this->expectException(DecryptException::class);
    $this->expectExceptionCode(Exception::DECRYPT_WECHAT_CIPHERTEXT_PARAMS_INVALID);
    
    // ... 测试代码
}

public function testDecryptResourceWithInvalidKey(): void
{
    // mch_secret_key 长度不正确
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_WECHAT_INVALID);
    
    // ... 测试代码
}
```

#### CertManager 测试

```php
// tests/CertManagerTest.php
public function testGetPublicCertFromFile(): void
{
    $result = CertManager::getPublicCert(__DIR__ . '/Cert/test.crt');
    $this->assertStringContainsString('BEGIN CERTIFICATE', $result);
}

public function testGetPublicCertFromString(): void
{
    $certContent = '-----BEGIN CERTIFICATE-----test-----END CERTIFICATE-----';
    $result = CertManager::getPublicCert($certContent);
    $this->assertEquals($certContent, $result);
}

public function testGetPublicCertCacheHit(): void
{
    $path = __DIR__ . '/Cert/test.crt';
    
    // 第一次调用
    $result1 = CertManager::getPublicCert($path);
    
    // 第二次调用（应命中缓存）
    $result2 = CertManager::getPublicCert($path);
    
    $this->assertEquals($result1, $result2);
}

public function testClearCache(): void
{
    CertManager::getPublicCert(__DIR__ . '/Cert/test.crt');
    CertManager::clearCache();
    
    // 验证缓存已清空
}
```

---

## 六、实施顺序

### Phase 1：基础设施（不破坏现有代码）

1. 创建 `src/Traits/` 目录
2. 创建 `CertHelperTrait.php`、`ProviderConfigTrait.php`
3. 创建各 Provider Trait 文件（方法名使用驼峰）
4. 创建 `src/Service/AbstractServiceProvider.php`

### Phase 2：迁移调用方

1. 修改所有 Plugin 文件，添加 `use Trait` 并改用 Trait 方法
2. 修改 Provider 文件
3. 修改 Shortcut 文件
4. 补充返回类型声明
5. 修改 ServiceProvider 继承抽象基类

### Phase 3：清理与测试

1. 删除 `src/Functions.php`
2. 添加 Trait 测试文件
3. 添加边界条件测试
4. 运行 `composer test` 确保全部通过
5. 运行 `composer analyse` 确保无新增错误

---

## 七、风险与回滚

### 风险点

1. **函数名变更** - 驼峰命名可能导致外部调用方需要更新（需在 CHANGELOG 中明确说明）
2. **Functions.php 删除** - 直接删除，不保留 deprecated，依赖此文件的外部代码需自行迁移
3. **缓存行为** - Swoole 常驻内存下证书缓存永不过期（可通过重启进程或调用 CertManager::clearCache() 刷新）

### 回滚方案

在主分支进行重构，通过 git 提交记录可随时回滚。发布前确保：
- 所有测试通过
- CHANGELOG.md 明确列出 Breaking Changes
- 文档更新说明函数迁移方式

---

## 八、验收标准

| 检查项 | 标准 |
|-------|------|
| 单元测试 | `composer test` 全部通过 |
| 静态分析 | `composer analyse` Level 5 无错误 |
| 代码覆盖率 | 新增 Trait 和边界测试覆盖率 > 80% |
| 文件删除 | Functions.php 已删除 |
| 类型声明 | 所有 Shortcut 方法有完整类型声明 |

---

## 九、附录：受影响文件清单

### 新增文件

| 文件路径 | 说明 |
|---------|------|
| `src/Traits/AlipayTrait.php` | 支付宝辅助方法 |
| `src/Traits/WechatTrait.php` | 微信辅助方法 |
| `src/Traits/UnipayTrait.php` | 银联辅助方法 |
| `src/Traits/JsbTrait.php` | 江苏银行辅助方法 |
| `src/Traits/DouyinTrait.php` | 抖音辅助方法 |
| `src/Traits/PaypalTrait.php` | PayPal 辅助方法 |
| `src/Traits/StripeTrait.php` | Stripe 辅助方法 |
| `src/Traits/ProviderConfigTrait.php` | 配置获取（共享） |
| `src/CertManager.php` | 证书缓存（独立类） |
| `src/Service/AbstractServiceProvider.php` | ServiceProvider 抽象基类 |
| `tests/Traits/*TraitTest.php` | Trait 测试（8个） |
| `tests/CertManagerTest.php` | CertManager 测试 |

### 修改文件

| 文件路径 | 修改内容 |
|---------|---------|
| `src/Plugin/**/*.php` | 添加 Trait，替换函数调用 |
| `src/Provider/*.php` | 添加 Trait，替换函数调用 |
| `src/Shortcut/**/*.php` | 补充类型声明 |
| `src/Service/*ServiceProvider.php` | 继承 AbstractServiceProvider（7个） |

### 删除文件

| 文件路径 | 说明 |
|---------|------|
| `src/Functions.php` | 辅助函数文件（829行） |

---

**设计文档完成，等待用户审核确认后进入实施计划阶段。**