<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class AlipayConfigTest extends TestCase
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
        $config = new AlipayConfig($this->validConfig);

        self::assertSame('default', $config->getTenant());
        self::assertSame('test_app_id', $config->getAppId());
        self::assertSame('test_secret', $config->getAppSecretCert());
        self::assertSame(Pay::MODE_NORMAL, $config->getMode());
    }

    public function testConstructWithTenant(): void
    {
        $config = new AlipayConfig($this->validConfig, 'custom_tenant');

        self::assertSame('custom_tenant', $config->getTenant());
    }

    public function testConstructMissingRequired(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 缺少支付宝配置 -- [app_id]');

        new AlipayConfig([
            // missing app_id
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
            'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
            'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
        ]);
    }

    public function testOptionalGetters(): void
    {
        $config = new AlipayConfig(array_merge($this->validConfig, [
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
        $config = new AlipayConfig($this->validConfig);

        self::assertNull($config->getNotifyUrl());
        self::assertNull($config->getReturnUrl());
        self::assertNull($config->getAppAuthToken());
        self::assertNull($config->getServiceProviderId());
    }

    public function testToArrayKeepsBackwardCompatibleSnakeCaseKeys(): void
    {
        $config = new AlipayConfig(array_merge($this->validConfig, [
            'notify_url' => 'https://notify.com',
            'return_url' => 'https://return.com',
            'app_auth_token' => 'auth_token',
            'service_provider_id' => 'sp_id',
        ]));

        self::assertSame([
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
            'tenant' => 'default',
        ], $config->toArray());
    }

    public function testModeSandbox(): void
    {
        $config = new AlipayConfig(array_merge($this->validConfig, [
            'mode' => Pay::MODE_SANDBOX,
        ]));

        self::assertSame(Pay::MODE_SANDBOX, $config->getMode());
    }
}
