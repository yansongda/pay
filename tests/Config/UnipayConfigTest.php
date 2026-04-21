<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class UnipayConfigTest extends TestCase
{
    private array $validConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validConfig = [
            'mch_cert_path' => __DIR__.'/../Cert/unipayAppPublicCert.crt',
            'mch_cert_password' => 'test_password',
        ];
    }

    public function testConstructValidConfig(): void
    {
        $config = new UnipayConfig($this->validConfig);

        self::assertSame('default', $config->getTenant());
        self::assertSame(Pay::MODE_NORMAL, $config->getMode());
    }

    public function testConstructWithTenant(): void
    {
        $config = new UnipayConfig($this->validConfig, 'custom_tenant');

        self::assertSame('custom_tenant', $config->getTenant());
    }

    public function testConstructMissingRequired(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 缺少银联配置 -- [mch_cert_path]');

        new UnipayConfig([
            // missing mch_cert_path
            'mch_cert_password' => 'test_password',
        ]);
    }

    public function testOptionalGetters(): void
    {
        $config = new UnipayConfig(array_merge($this->validConfig, [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => 'secret_key',
            'notify_url' => 'https://notify.com',
            'return_url' => 'https://return.com',
            'unipay_public_cert_path' => '/path/to/cert',
            'certs' => ['cert1' => 'value1'],
        ]));

        self::assertSame('test_mch_id', $config->getMchId());
        self::assertSame('secret_key', $config->getMchSecretKey());
        self::assertSame('https://notify.com', $config->getNotifyUrl());
        self::assertSame('https://return.com', $config->getReturnUrl());
        self::assertSame('/path/to/cert', $config->getUnipayPublicCertPath());
        self::assertSame(['cert1' => 'value1'], $config->getCerts());
    }

    public function testOptionalGettersNull(): void
    {
        $config = new UnipayConfig($this->validConfig);

        self::assertNull($config->getMchId());
        self::assertNull($config->getMchSecretKey());
        self::assertNull($config->getNotifyUrl());
        self::assertNull($config->getReturnUrl());
        self::assertNull($config->getUnipayPublicCertPath());
        self::assertSame([], $config->getCerts());
    }

    public function testModeSandbox(): void
    {
        $config = new UnipayConfig(array_merge($this->validConfig, [
            'mode' => Pay::MODE_SANDBOX,
        ]));

        self::assertSame(Pay::MODE_SANDBOX, $config->getMode());
    }
}
