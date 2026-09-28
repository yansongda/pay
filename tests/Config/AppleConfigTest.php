<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AppleConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class AppleConfigTest extends TestCase
{
    private array $validConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validConfig = [
            'merchant_id' => 'merchant.com.example.app',
            'payment_processing_cert' => '/path/to/payment-processing-cert.pem',
        ];
    }

    public function testConstructValidConfig(): void
    {
        $config = new AppleConfig(array_merge($this->validConfig, [
            'payment_processing_cert_passphrase' => 'passphrase',
            'apple_root_ca' => '/path/to/apple-root-ca.pem',
            'apple_intermediate_ca' => '/path/to/apple-intermediate-ca.pem',
            'issuer_id' => 'issuer-id-123',
            'bundle_id' => 'com.example.app',
            'api_key_id' => 'key-id-123',
            'api_private_key' => '/path/to/api.key',
            'notify_url' => 'https://notify.com',
            'mode' => Pay::MODE_SANDBOX,
        ]));

        self::assertSame('default', $config->getTenant());
        self::assertSame('merchant.com.example.app', $config->getMerchantId());
        self::assertSame('/path/to/payment-processing-cert.pem', $config->getPaymentProcessingCert());
        self::assertSame('passphrase', $config->getPaymentProcessingCertPassphrase());
        self::assertSame('/path/to/apple-root-ca.pem', $config->getAppleRootCa());
        self::assertSame('/path/to/apple-intermediate-ca.pem', $config->getAppleIntermediateCa());
        self::assertSame('issuer-id-123', $config->getIssuerId());
        self::assertSame('com.example.app', $config->getBundleId());
        self::assertSame('key-id-123', $config->getApiKeyId());
        self::assertSame('/path/to/api.key', $config->getApiPrivateKey());
        self::assertSame('https://notify.com', $config->getNotifyUrl());
        self::assertSame(Pay::MODE_SANDBOX, $config->getMode());
    }

    public function testDefaultAppleRootCaAndIntermediateCa(): void
    {
        // 未配置时回退内置证书（BUG 修复：文档宣称的「默认内置」此前为死代码）
        $config = new AppleConfig($this->validConfig);

        $rootCa = $config->getAppleRootCa();

        self::assertSame(realpath(dirname(__DIR__, 2).'/src/Certificate/AppleRootCA-G3.pem'), realpath($rootCa));
        self::assertFileExists($rootCa);
        self::assertNotFalse(openssl_x509_parse((string) file_get_contents($rootCa)));

        $intermediateCa = $config->getAppleIntermediateCa();

        self::assertSame(realpath(dirname(__DIR__, 2).'/src/Certificate/AppleAAICAG3.pem'), realpath($intermediateCa));
        self::assertFileExists($intermediateCa);
        self::assertNotFalse(openssl_x509_parse((string) file_get_contents($intermediateCa)));
    }

    public function testConstructMissingMerchantId(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_APPLE_INVALID);
        $this->expectExceptionMessage('配置异常: 缺少 Apple 配置 -- [merchant_id]');

        $config = new AppleConfig([
            'payment_processing_cert' => '/path/to/payment-processing-cert.pem',
        ]);
        $config->validate();
    }

    public function testConstructMissingPaymentProcessingCert(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_APPLE_INVALID);
        $this->expectExceptionMessage('配置异常: 缺少 Apple 配置 -- [payment_processing_cert]');

        $config = new AppleConfig([
            'merchant_id' => 'merchant.com.example.app',
        ]);
        $config->validate();
    }

    public function testConstructPartialApiConfig(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_APPLE_INVALID);
        $this->expectExceptionMessage('配置异常: Apple App Store Server API 配置不完整 -- [issuer_id]/[bundle_id]/[api_key_id]/[api_private_key] 需同时配置');

        $config = new AppleConfig(array_merge($this->validConfig, [
            'issuer_id' => 'issuer-id-123',
        ]));
        $config->validate();
    }

    public function testConstructWithoutApiConfig(): void
    {
        $config = new AppleConfig($this->validConfig);

        self::assertNull($config->getIssuerId());
        self::assertNull($config->getBundleId());
        self::assertNull($config->getApiKeyId());
        self::assertNull($config->getApiPrivateKey());
        self::assertNull($config->getNotifyUrl());
        // 未配置时回退内置证书（不再返回 null）
        self::assertFileExists($config->getAppleRootCa());
        self::assertFileExists($config->getAppleIntermediateCa());
        self::assertSame(Pay::MODE_NORMAL, $config->getMode());

        $config->validate();
    }

    public function testInvalidModeThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_PROVIDER_INVALID);

        $config = new AppleConfig(array_merge($this->validConfig, [
            'mode' => 999999,
        ]));
        $config->validate();
    }
}
