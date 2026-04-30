<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Tests\TestCase;

class ConfigTest extends TestCase
{
    public function testConstructWithArray(): void
    {
        $config = new Config([
            'wechat' => [
                'default' => [
                    'app_id' => 'test_app_id',
                    'mch_id' => 'test_mch_id',
                    'mch_secret_key' => '12345678901234567890123456789012',
                    'mch_secret_cert' => 'test_cert',
                    'mch_public_cert_path' => 'test_path',
                    'notify_url' => 'https://test.com',
                ],
            ],
        ]);

        $wechatConfig = $config->get('wechat.default');
        self::assertInstanceOf(WechatConfig::class, $wechatConfig);
        self::assertSame('test_mch_id', $wechatConfig->getMchId());
    }

    public function testGetProviderConfig(): void
    {
        $config = new Config([
            'alipay' => [
                'default' => [
                    'app_id' => 'test_app_id',
                    'app_secret_cert' => 'test_secret',
                    'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
                    'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
                    'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
                ],
            ],
        ]);

        /** @var AlipayConfig $alipayConfig */
        $alipayConfig = $config->getProviderConfig('alipay');
        self::assertInstanceOf(AlipayConfig::class, $alipayConfig);
        self::assertSame('test_app_id', $alipayConfig->getAppId());
    }

    public function testGetProviderConfigWithTenant(): void
    {
        $config = new Config([
            'alipay' => [
                'tenant1' => [
                    'app_id' => 'tenant1_app_id',
                    'app_secret_cert' => 'test_secret',
                    'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
                    'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
                    'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
                ],
            ],
        ]);

        /** @var AlipayConfig $alipayConfig */
        $alipayConfig = $config->getProviderConfig('alipay', 'tenant1');
        self::assertSame('tenant1_app_id', $alipayConfig->getAppId());
    }

    public function testGetProviderConfigUnknownProvider(): void
    {
        $config = new Config([]);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_PROVIDER_INVALID);
        $this->expectExceptionMessage('配置异常: 未知的 Provider - unknown');

        $config->getProviderConfig('unknown');
    }

    public function testGetProviderConfigMissingTenant(): void
    {
        $config = new Config([
            'alipay' => [
                'default' => [
                    'app_id' => 'test_app_id',
                    'app_secret_cert' => 'test_secret',
                    'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
                    'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
                    'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
                ],
            ],
        ]);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_PROVIDER_INVALID);
        $this->expectExceptionMessage('配置异常: alipay.missing_tenant 配置不存在');

        $config->getProviderConfig('alipay', 'missing_tenant');
    }

    public function testConfigObjectNotConverted(): void
    {
        // 如果已经是 Config 对象，不应该再次转换
        $existingConfig = new WechatConfig([
            'app_id' => 'existing_app_id',
            'mch_id' => 'existing_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'existing_cert',
            'mch_public_cert_path' => 'existing_path',
            'notify_url' => 'https://existing.com',
        ], 'test_tenant');

        $config = new Config([
            'wechat' => [
                'test_tenant' => $existingConfig,
            ],
        ]);

        $wechatConfig = $config->getProviderConfig('wechat', 'test_tenant');
        self::assertSame($existingConfig, $wechatConfig);
    }

    public function testProviderConfigToArrayUsesSnakeCaseExternalKeys(): void
    {
        $config = new Config([
            'alipay' => [
                'default' => [
                    'app_id' => 'test_app_id',
                    'app_secret_cert' => 'test_secret',
                    'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
                    'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
                    'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
                ],
            ],
        ]);

        /** @var AlipayConfig $alipayConfig */
        $alipayConfig = $config->getProviderConfig('alipay');
        self::assertArrayNotHasKey('appPublicCertSn', $alipayConfig->toArray());
        self::assertArrayNotHasKey('alipayRootCertSn', $alipayConfig->toArray());
    }
}
