<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Config\AlipayV3Config;
use Yansongda\Pay\Tests\TestCase;

class AlipayV3ConfigTest extends TestCase
{
    public function testPublicKeyModeValidateSuccess(): void
    {
        $config = new AlipayV3Config([
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'alipay_public_key' => 'test_public_key',
        ]);

        self::assertSame(AlipayConfig::VERSION_V3, $config->getVersion());

        $config->validate();

        self::assertSame('test_public_key', $config->getAlipayPublicKey());
    }

    public function testPublicKeyModeMissingAlipayPublicKey(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 缺少支付宝配置 -- [alipay_public_key]');

        $config = new AlipayV3Config([
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
        ]);
        $config->validate();
    }

    public function testCertModeValidateSuccess(): void
    {
        $config = new AlipayV3Config([
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
            'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
        ]);

        $config->validate();

        self::assertSame(__DIR__.'/../Cert/alipayAppPublicCert.crt', $config->getAppPublicCertPath());
        self::assertSame(__DIR__.'/../Cert/alipayPublicCert.crt', $config->getAlipayPublicCertPath());
    }

    public function testCertModeRootCertIsNotRequired(): void
    {
        // V3 协议无 root-cert-sn，配置数组中的支付宝根证书会被忽略
        $config = new AlipayV3Config([
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
            'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
            'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
        ]);

        $config->validate();

        self::assertNull($config->getAlipayPublicKey());
    }

    public function testCertModeMissingAlipayPublicCertPath(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 缺少支付宝配置 -- [alipay_public_cert_path]');

        $config = new AlipayV3Config([
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
        ]);
        $config->validate();
    }

    public function testFromArrayWithV3(): void
    {
        $config = AlipayConfig::fromArray([
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'alipay_public_key' => 'test_public_key',
            'version' => AlipayConfig::VERSION_V3,
        ], 'tenant_v3');

        self::assertInstanceOf(AlipayV3Config::class, $config);
        self::assertInstanceOf(AlipayConfig::class, $config);
        self::assertSame(AlipayConfig::VERSION_V3, $config->getVersion());
        self::assertSame('tenant_v3', $config->getTenant());
    }

    public function testFromArrayInvalidVersionThrows(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: version 仅支持 v2 或 v3，当前为 [v4]');

        AlipayConfig::fromArray(['app_id' => 'test_app_id', 'version' => 'v4']);
    }
}
