<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AllinpayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class AllinpayConfigTest extends TestCase
{
    private array $validConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validConfig = [
            'cusid' => '9900000',
            'appid' => '000000',
            'mch_secret_key' => '/path/to/secret.pem',
            'allinpay_public_key' => '/path/to/allinpay.pem',
        ];
    }

    public function testConstructValidConfig(): void
    {
        $config = new AllinpayConfig($this->validConfig);

        self::assertSame('default', $config->getTenant());
        self::assertSame('9900000', $config->getCusid());
        self::assertSame('000000', $config->getAppid());
        self::assertNull($config->getOrgid());
        self::assertSame('/path/to/secret.pem', $config->getMchSecretKey());
        self::assertSame('/path/to/allinpay.pem', $config->getAllinpayPublicKey());
        self::assertSame(Pay::MODE_NORMAL, $config->getMode());
    }

    public function testConstructWithTenant(): void
    {
        $config = new AllinpayConfig($this->validConfig, 'custom_tenant');

        self::assertSame('custom_tenant', $config->getTenant());
    }

    public function testConstructMissingRequired(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_ALLINPAY_INVALID);

        $config = new AllinpayConfig([
            'appid' => '000000',
            'mch_secret_key' => '/path/to/secret.pem',
            'allinpay_public_key' => '/path/to/allinpay.pem',
        ]);
        $config->validate();
    }

    public function testOptionalGetters(): void
    {
        $config = new AllinpayConfig(array_merge($this->validConfig, [
            'orgid' => '999999',
            'notify_url' => 'https://notify.com',
            'mode' => Pay::MODE_SANDBOX,
        ]));

        self::assertSame('999999', $config->getOrgid());
        self::assertSame('https://notify.com', $config->getNotifyUrl());
        self::assertSame(Pay::MODE_SANDBOX, $config->getMode());
    }

    public function testServiceModeThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_PROVIDER_INVALID);

        $config = new AllinpayConfig(array_merge($this->validConfig, [
            'mode' => Pay::MODE_SERVICE,
        ]));
        $config->validate();
    }
}
