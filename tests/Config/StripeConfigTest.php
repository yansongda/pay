<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\StripeConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class StripeConfigTest extends TestCase
{
    private array $validConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validConfig = [
            'secret_key' => 'sk_test_123',
        ];
    }

    public function testConstructValidConfig(): void
    {
        $config = new StripeConfig($this->validConfig);

        self::assertSame('default', $config->getTenant());
        self::assertSame('sk_test_123', $config->getSecretKey());
        self::assertSame(Pay::MODE_NORMAL, $config->getMode());
    }

    public function testConstructWithTenant(): void
    {
        $config = new StripeConfig($this->validConfig, 'custom_tenant');

        self::assertSame('custom_tenant', $config->getTenant());
    }

    public function testConstructMissingRequired(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 缺少 Stripe 配置 -- [secret_key]');

        new StripeConfig([]);
    }

    public function testOptionalGetters(): void
    {
        $config = new StripeConfig(array_merge($this->validConfig, [
            'webhook_secret' => 'whsec_123',
            'notify_url' => 'https://notify.com',
            'success_url' => 'https://success.com',
            'cancel_url' => 'https://cancel.com',
        ]));

        self::assertSame('whsec_123', $config->getWebhookSecret());
        self::assertSame('https://notify.com', $config->getNotifyUrl());
        self::assertSame('https://success.com', $config->getSuccessUrl());
        self::assertSame('https://cancel.com', $config->getCancelUrl());
    }

    public function testOptionalGettersNull(): void
    {
        $config = new StripeConfig($this->validConfig);

        self::assertNull($config->getWebhookSecret());
        self::assertNull($config->getNotifyUrl());
        self::assertNull($config->getSuccessUrl());
        self::assertNull($config->getCancelUrl());
    }

    public function testModeSandbox(): void
    {
        $config = new StripeConfig(array_merge($this->validConfig, [
            'mode' => Pay::MODE_SANDBOX,
        ]));

        self::assertSame(Pay::MODE_SANDBOX, $config->getMode());
    }
}
