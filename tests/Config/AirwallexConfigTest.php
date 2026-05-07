<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AirwallexConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class AirwallexConfigTest extends TestCase
{
    private array $validConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validConfig = [
            'client_id' => 'airwallex_client_id',
            'api_key' => 'airwallex_api_key',
        ];
    }

    public function testConstructValidConfig(): void
    {
        $config = new AirwallexConfig($this->validConfig);

        self::assertSame('default', $config->getTenant());
        self::assertSame('airwallex_client_id', $config->getClientId());
        self::assertSame('airwallex_api_key', $config->getApiKey());
        self::assertSame(Pay::MODE_NORMAL, $config->getMode());
    }

    public function testConstructWithTenant(): void
    {
        $config = new AirwallexConfig($this->validConfig, 'secondary');

        self::assertSame('secondary', $config->getTenant());
    }

    public function testConstructMissingClientId(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置错误: Airwallex 配置缺少 [client_id]');

        new AirwallexConfig(['api_key' => 'airwallex_api_key']);
    }

    public function testConstructMissingApiKey(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置错误: Airwallex 配置缺少 [api_key]');

        new AirwallexConfig(['client_id' => 'airwallex_client_id']);
    }

    public function testOptionalGetters(): void
    {
        $config = new AirwallexConfig(array_merge($this->validConfig, [
            'webhook_secret' => 'airwallex_webhook_secret',
            'return_url' => 'https://pay.yansongda.cn/airwallex/return',
            'api_version' => '2024-06-14',
            'on_behalf_of' => 'acct_123',
            'mode' => Pay::MODE_SANDBOX,
        ]));

        self::assertSame('airwallex_webhook_secret', $config->getWebhookSecret());
        self::assertSame('https://pay.yansongda.cn/airwallex/return', $config->getReturnUrl());
        self::assertSame('2024-06-14', $config->getApiVersion());
        self::assertSame('acct_123', $config->getOnBehalfOf());
        self::assertSame(Pay::MODE_SANDBOX, $config->getMode());
    }

    public function testAccessTokenCacheFields(): void
    {
        $config = new AirwallexConfig($this->validConfig);

        $config->setAccessToken('airwallex_access_token');
        $config->setAccessTokenExpiry('1893456000');

        self::assertSame('airwallex_access_token', $config->getAccessToken());
        self::assertSame(1893456000, $config->getAccessTokenExpiry());
    }

    public function testNullableOptionalFields(): void
    {
        $config = new AirwallexConfig(array_merge($this->validConfig, [
            'webhook_secret' => null,
            'return_url' => null,
            'api_version' => null,
            'on_behalf_of' => null,
            'access_token' => null,
            'access_token_expiry' => null,
        ]));

        self::assertNull($config->getWebhookSecret());
        self::assertNull($config->getReturnUrl());
        self::assertNull($config->getApiVersion());
        self::assertNull($config->getOnBehalfOf());
        self::assertNull($config->getAccessToken());
        self::assertNull($config->getAccessTokenExpiry());
    }
}
