<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\JsbConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class JsbConfigTest extends TestCase
{
    private array $validConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validConfig = [
            'partner_id' => 'partner_123',
            'public_key_code' => '00',
            'mch_secret_cert_path' => '/path/to/secret.pem',
            'jsb_public_cert_path' => '/path/to/jsb.pem',
        ];
    }

    public function testConstructValidConfig(): void
    {
        $config = new JsbConfig($this->validConfig);

        self::assertSame('default', $config->getTenant());
        self::assertSame('partner_123', $config->getPartnerId());
        self::assertSame('00', $config->getPublicKeyCode());
        self::assertSame('/path/to/secret.pem', $config->getMchSecretCertPath());
        self::assertSame('/path/to/jsb.pem', $config->getJsbPublicCertPath());
        self::assertSame(Pay::MODE_NORMAL, $config->getMode());
    }

    public function testConstructWithTenant(): void
    {
        $config = new JsbConfig($this->validConfig, 'custom_tenant');

        self::assertSame('custom_tenant', $config->getTenant());
    }

    public function testConstructMissingRequired(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 缺少江苏银行配置 -- [partner_id]');

        new JsbConfig([
            // missing partner_id
            'public_key_code' => '00',
            'mch_secret_cert_path' => '/path/to/secret.pem',
            'jsb_public_cert_path' => '/path/to/jsb.pem',
        ]);
    }

    public function testOptionalGetters(): void
    {
        $config = new JsbConfig(array_merge($this->validConfig, [
            'svr_code' => 'svr_123',
            'mch_public_cert_path' => '/path/to/public.pem',
            'notify_url' => 'https://notify.com',
        ]));

        self::assertSame('svr_123', $config->getSvrCode());
        self::assertSame('/path/to/public.pem', $config->getMchPublicCertPath());
        self::assertSame('https://notify.com', $config->getNotifyUrl());
    }

    public function testOptionalGettersNull(): void
    {
        $config = new JsbConfig($this->validConfig);

        self::assertSame('', $config->getSvrCode());
        self::assertNull($config->getMchPublicCertPath());
        self::assertNull($config->getNotifyUrl());
    }

    public function testModeSandbox(): void
    {
        $config = new JsbConfig(array_merge($this->validConfig, [
            'mode' => Pay::MODE_SANDBOX,
        ]));

        self::assertSame(Pay::MODE_SANDBOX, $config->getMode());
    }
}
