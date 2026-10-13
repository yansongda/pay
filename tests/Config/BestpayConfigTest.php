<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\BestpayConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Tests\TestCase;

class BestpayConfigTest extends TestCase
{
    private array $validConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validConfig = [
            'merchant_no' => '1234567890',
            'institution_code' => 'INS001',
            'mch_secret_cert_path' => '/path/to/secret.pem',
            'mch_secret_cert_password' => 'secret-password',
            'bestpay_public_cert_path' => '/path/to/bestpay.pem',
        ];
    }

    public function testConstructValidConfig(): void
    {
        $config = new BestpayConfig($this->validConfig);

        self::assertSame('default', $config->getTenant());
        self::assertSame('1234567890', $config->getMerchantNo());
        self::assertSame('INS001', $config->getInstitutionCode());
        self::assertSame('MERCHANT', $config->getInstitutionType());
        self::assertSame('/path/to/secret.pem', $config->getMchSecretCertPath());
        self::assertSame('secret-password', $config->getMchSecretCertPassword());
        self::assertSame('/path/to/bestpay.pem', $config->getBestpayPublicCertPath());
        self::assertSame('1.0.3', $config->getApiVersion());
        self::assertNull($config->getNotifyUrl());
        self::assertNull($config->getReturnUrl());
        self::assertSame(PayMode::NORMAL, $config->getMode());
    }

    public function testConstructWithTenant(): void
    {
        $config = new BestpayConfig($this->validConfig, 'custom_tenant');

        self::assertSame('custom_tenant', $config->getTenant());
    }

    public function testConstructMissingRequired(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_BESTPAY_INVALID);

        $config = new BestpayConfig([
            'merchant_no' => '1234567890',
            'institution_code' => 'INS001',
            'mch_secret_cert_path' => '/path/to/secret.pem',
            'mch_secret_cert_password' => 'secret-password',
            // missing bestpay_public_cert_path
        ]);
        $config->validate();
    }

    public function testOptionalGetters(): void
    {
        $config = new BestpayConfig(array_merge($this->validConfig, [
            'institution_type' => 'SUB_MERCHANT',
            'api_version' => '1.0.4',
            'notify_url' => 'https://notify.com',
            'return_url' => 'https://return.com',
        ]));

        self::assertSame('SUB_MERCHANT', $config->getInstitutionType());
        self::assertSame('1.0.4', $config->getApiVersion());
        self::assertSame('https://notify.com', $config->getNotifyUrl());
        self::assertSame('https://return.com', $config->getReturnUrl());
    }

    public function testModeSandbox(): void
    {
        $config = new BestpayConfig(array_merge($this->validConfig, [
            // 兼容历史 int 写法：0/1/2
            'mode' => 1,
        ]));

        self::assertSame(PayMode::SANDBOX, $config->getMode());
    }

    public function testModeWithPayModeEnum(): void
    {
        $config = new BestpayConfig(array_merge($this->validConfig, [
            'mode' => PayMode::SANDBOX,
        ]));

        self::assertSame(PayMode::SANDBOX, $config->getMode());
    }

    public function testServiceModeThrowsException(): void
    {
        // 翼支付一期无服务商模式，mode = SERVICE 应被校验拦截
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_PROVIDER_INVALID);
        $this->expectExceptionMessage('配置异常: [mode] 配置不合法');

        $config = new BestpayConfig(array_merge($this->validConfig, [
            'mode' => PayMode::SERVICE,
        ]));
        $config->validate();
    }
}
