<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class DouyinConfigTest extends TestCase
{
    private array $validConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validConfig = [
            'mini_app_id' => 'tt123456',
            'mch_secret_token' => 'token_abc',
            'mch_secret_salt' => 'salt_xyz',
        ];
    }

    public function testConstructValidConfig(): void
    {
        $config = new DouyinConfig($this->validConfig);

        self::assertSame('default', $config->getTenant());
        self::assertSame('tt123456', $config->getMiniAppId());
        self::assertSame('token_abc', $config->getMchSecretToken());
        self::assertSame('salt_xyz', $config->getMchSecretSalt());
        self::assertSame(Pay::MODE_NORMAL, $config->getMode());
    }

    public function testConstructWithTenant(): void
    {
        $config = new DouyinConfig($this->validConfig, 'custom_tenant');

        self::assertSame('custom_tenant', $config->getTenant());
    }

    public function testConstructMissingMiniAppId(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 缺少抖音配置 -- [mini_app_id]');

        new DouyinConfig([
            // missing mini_app_id
            'mch_secret_token' => 'token_abc',
            'mch_secret_salt' => 'salt_xyz',
        ]);
    }

    public function testOptionalGetters(): void
    {
        $config = new DouyinConfig(array_merge($this->validConfig, [
            'mch_id' => 'mch_123',
            'thirdparty_id' => 'tp_456',
            'notify_url' => 'https://notify.com',
        ]));

        self::assertSame('mch_123', $config->getMchId());
        self::assertSame('tp_456', $config->getThirdpartyId());
        self::assertSame('https://notify.com', $config->getNotifyUrl());
    }

    public function testOptionalGettersNull(): void
    {
        $config = new DouyinConfig($this->validConfig);

        self::assertNull($config->getMchId());
        self::assertNull($config->getThirdpartyId());
        self::assertNull($config->getNotifyUrl());
    }
}
