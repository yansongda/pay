<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class DouyinConfigTest extends TestCase
{
    private array $validConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validConfig = [
            'app_id' => 'tt123456',
            'app_secret' => 'test_app_secret',
            'app_private_key' => 'test_app_private_key',
            'douyin_public_key' => 'test_douyin_public_key',
        ];
    }

    public function testConstructValidConfig(): void
    {
        $config = new DouyinConfig($this->validConfig);

        self::assertSame('default', $config->getTenant());
        self::assertSame('tt123456', $config->getAppId());
        self::assertSame('test_app_secret', $config->getAppSecret());
        self::assertSame('test_app_private_key', $config->getAppPrivateKey());
        self::assertSame('test_douyin_public_key', $config->getDouyinPublicKey());
        self::assertSame(Pay::MODE_NORMAL, $config->getMode());
    }

    public function testConstructWithTenant(): void
    {
        $config = new DouyinConfig($this->validConfig, 'custom_tenant');

        self::assertSame('custom_tenant', $config->getTenant());
    }

    public function testConstructMissingAppId(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_DOUYIN_INVALID);
        $this->expectExceptionMessage('配置异常: 缺少抖音配置 -- [app_id]');

        $config = new DouyinConfig([
            // missing app_id
            'app_secret' => 'test_app_secret',
        ]);
        $config->validate();
    }

    public function testConstructMissingAppSecret(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_DOUYIN_INVALID);
        $this->expectExceptionMessage('配置异常: 缺少抖音配置 -- [app_secret]');

        $config = new DouyinConfig([
            'app_id' => 'tt123456',
            // missing app_secret
        ]);
        $config->validate();
    }

    public function testOptionalGetters(): void
    {
        $config = new DouyinConfig(array_merge($this->validConfig, [
            'notify_url' => 'https://notify.com',
        ]));

        self::assertSame('https://notify.com', $config->getNotifyUrl());
    }

    public function testOptionalGettersNull(): void
    {
        $config = new DouyinConfig($this->validConfig);

        self::assertNull($config->getNotifyUrl());
    }

    public function testModeSandbox(): void
    {
        $config = new DouyinConfig(array_merge($this->validConfig, [
            'mode' => Pay::MODE_SANDBOX,
        ]));

        self::assertSame(Pay::MODE_SANDBOX, $config->getMode());
    }

    public function testInvalidModeThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_PROVIDER_INVALID);
        $this->expectExceptionMessage('配置异常: [mode] 配置不合法');

        $config = new DouyinConfig(array_merge($this->validConfig, [
            'mode' => 99999,
        ]));
        $config->validate();
    }
}
