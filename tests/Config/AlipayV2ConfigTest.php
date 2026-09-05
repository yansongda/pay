<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Config\AlipayV2Config;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class AlipayV2ConfigTest extends TestCase
{
    private array $validConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validConfig = [
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
            'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
            'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
        ];
    }

    public function testConstructValidConfig(): void
    {
        $config = new AlipayV2Config($this->validConfig);

        self::assertSame('default', $config->getTenant());
        self::assertSame('test_app_id', $config->getAppId());
        self::assertSame('test_secret', $config->getAppSecretCert());
        self::assertSame(Pay::MODE_NORMAL, $config->getMode());
        self::assertSame(AlipayConfig::VERSION_V2, $config->getVersion());
    }

    public function testConstructWithTenant(): void
    {
        $config = new AlipayV2Config($this->validConfig, 'custom_tenant');

        self::assertSame('custom_tenant', $config->getTenant());
    }

    public function testConstructMissingRequired(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 缺少支付宝配置 -- [app_id]');

        $config = new AlipayV2Config([
            // missing app_id
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
            'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
            'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
        ]);
        $config->validate();
    }

    public function testOptionalGetters(): void
    {
        $config = new AlipayV2Config(array_merge($this->validConfig, [
            'notify_url' => 'https://notify.com',
            'return_url' => 'https://return.com',
            'app_auth_token' => 'auth_token',
            'service_provider_id' => 'sp_id',
        ]));

        self::assertSame('https://notify.com', $config->getNotifyUrl());
        self::assertSame('https://return.com', $config->getReturnUrl());
        self::assertSame('auth_token', $config->getAppAuthToken());
        self::assertSame('sp_id', $config->getServiceProviderId());
    }

    public function testOptionalGettersNull(): void
    {
        $config = new AlipayV2Config($this->validConfig);

        self::assertNull($config->getNotifyUrl());
        self::assertNull($config->getReturnUrl());
        self::assertNull($config->getAppAuthToken());
        self::assertNull($config->getServiceProviderId());
    }

    public function testToArrayKeepsBackwardCompatibleSnakeCaseKeys(): void
    {
        $config = new AlipayV2Config(array_merge($this->validConfig, [
            'notify_url' => 'https://notify.com',
            'return_url' => 'https://return.com',
            'app_auth_token' => 'auth_token',
            'service_provider_id' => 'sp_id',
        ]));

        $array = $config->toArray();
        ksort($array);

        $expected = [
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
            'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
            'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
            'notify_url' => 'https://notify.com',
            'return_url' => 'https://return.com',
            'app_auth_token' => 'auth_token',
            'service_provider_id' => 'sp_id',
            'mode' => Pay::MODE_NORMAL,
            'version' => 'v2',
            'tenant' => 'default',
        ];
        ksort($expected);

        // 拆分后属性分布在基类与子类，仅断言键值集合，不断言键序
        self::assertSame($expected, $array);
    }

    public function testModeSandbox(): void
    {
        $config = new AlipayV2Config(array_merge($this->validConfig, [
            'mode' => Pay::MODE_SANDBOX,
        ]));

        self::assertSame(Pay::MODE_SANDBOX, $config->getMode());
    }

    public function testDefaultVersionIsV2(): void
    {
        $config = new AlipayV2Config($this->validConfig);

        self::assertSame(AlipayConfig::VERSION_V2, $config->getVersion());
    }

    public function testConfigDefaultsToV2Config(): void
    {
        $config = new \Yansongda\Pay\Config([
            'alipay' => [
                'default' => $this->validConfig,
            ],
        ]);

        $alipayConfig = $config->getProviderConfig('alipay');

        self::assertInstanceOf(AlipayV2Config::class, $alipayConfig);
        self::assertSame(AlipayConfig::VERSION_V2, $alipayConfig->getVersion());
        self::assertSame('default', $alipayConfig->getTenant());
    }

    public function testConfigWithExplicitV2(): void
    {
        $config = new \Yansongda\Pay\Config([
            'alipay' => [
                'tenant_v2' => array_merge($this->validConfig, [
                    'version' => AlipayConfig::VERSION_V2,
                ]),
            ],
        ]);

        $alipayConfig = $config->getProviderConfig('alipay', 'tenant_v2');

        self::assertInstanceOf(AlipayV2Config::class, $alipayConfig);
        self::assertSame('tenant_v2', $alipayConfig->getTenant());
    }
}
