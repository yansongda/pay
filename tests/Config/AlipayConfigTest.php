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

        $config = new AlipayConfig([
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
            'version' => 'v2',
            'alipay_public_key' => null,
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

    public function testDefaultVersionIsV2(): void
    {
        $config = new AlipayConfig($this->validConfig);

        self::assertSame('v2', $config->getVersion());
        self::assertNull($config->getAlipayPublicKey());
    }

    public function testV3PublicKeyModeValidateSuccess(): void
    {
        $config = new AlipayConfig([
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'alipay_public_key' => 'test_public_key',
            'version' => 'v3',
        ]);

        self::assertSame('v3', $config->getVersion());

        $config->validate();

        self::assertSame('test_public_key', $config->getAlipayPublicKey());
    }

    public function testV3PublicKeyModeMissingAlipayPublicKey(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 缺少支付宝配置 -- [alipay_public_key]');

        $config = new AlipayConfig([
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'version' => 'v3',
        ]);
        $config->validate();
    }

    public function testV3CertModeValidateSuccess(): void
    {
        $config = new AlipayConfig([
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
            'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
            'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
            'version' => 'v3',
        ]);

        $config->validate();

        self::assertSame('v3', $config->getVersion());
    }

    public function testInvalidVersionThrows(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: version 仅支持 v2 或 v3，当前为 [v4]');

        $config = new AlipayConfig(array_merge($this->validConfig, ['version' => 'v4']));
        $config->validate();
    }
}
