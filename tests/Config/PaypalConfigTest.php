<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\PaypalConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class PaypalConfigTest extends TestCase
{
    private array $validConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validConfig = [
            'client_id' => 'test_client_id',
            'app_secret' => 'test_app_secret',
        ];
    }

    public function testConstructValidConfig(): void
    {
        $config = new PaypalConfig($this->validConfig);

        self::assertSame('default', $config->getTenant());
        self::assertSame('test_client_id', $config->getClientId());
        self::assertSame('test_app_secret', $config->getAppSecret());
        self::assertSame(Pay::MODE_NORMAL, $config->getMode());
    }

    public function testConstructWithTenant(): void
    {
        $config = new PaypalConfig($this->validConfig, 'custom_tenant');

        self::assertSame('custom_tenant', $config->getTenant());
    }

    public function testConstructMissingRequired(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 缺少 PayPal 配置 -- [client_id]');

        new PaypalConfig([
            // missing client_id
            'app_secret' => 'test_app_secret',
        ]);
    }

    public function testOptionalGetters(): void
    {
        $config = new PaypalConfig(array_merge($this->validConfig, [
            'webhook_id' => 'webhook_123',
            'notify_url' => 'https://notify.com',
            'return_url' => 'https://return.com',
            'cancel_url' => 'https://cancel.com',
            'brand_name' => 'Test Brand',
        ]));

        self::assertSame('webhook_123', $config->getWebhookId());
        self::assertSame('https://notify.com', $config->getNotifyUrl());
        self::assertSame('https://return.com', $config->getReturnUrl());
        self::assertSame('https://cancel.com', $config->getCancelUrl());
        self::assertSame('Test Brand', $config->getBrandName());
    }

    public function testOptionalGettersNull(): void
    {
        $config = new PaypalConfig($this->validConfig);

        self::assertNull($config->getWebhookId());
        self::assertNull($config->getNotifyUrl());
        self::assertNull($config->getReturnUrl());
        self::assertNull($config->getCancelUrl());
        self::assertNull($config->getBrandName());
        self::assertNull($config->getAccessToken());
        self::assertNull($config->getAccessTokenExpiry());
    }

    public function testAccessToken(): void
    {
        $config = new PaypalConfig(array_merge($this->validConfig, [
            '_access_token' => 'token_abc',
            '_access_token_expiry' => 1234567890,
        ]));

        self::assertSame('token_abc', $config->getAccessToken());
        self::assertSame(1234567890, $config->getAccessTokenExpiry());
    }

    public function testModeSandbox(): void
    {
        $config = new PaypalConfig(array_merge($this->validConfig, [
            'mode' => Pay::MODE_SANDBOX,
        ]));

        self::assertSame(Pay::MODE_SANDBOX, $config->getMode());
    }
}
