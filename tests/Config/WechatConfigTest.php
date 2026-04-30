<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class WechatConfigTest extends TestCase
{
    private array $validConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
    }

    public function testConstructValidConfig(): void
    {
        $config = new WechatConfig($this->validConfig);

        self::assertSame('default', $config->getTenant());
        self::assertSame('test_mch_id', $config->getMchId());
        self::assertSame('12345678901234567890123456789012', $config->getMchSecretKey());
        self::assertSame('test_cert', $config->getMchSecretCert());
        self::assertSame('test_path', $config->getMchPublicCertPath());
        self::assertSame('https://test.com', $config->getNotifyUrl());
        self::assertSame(Pay::MODE_NORMAL, $config->getMode());
    }

    public function testConstructWithTenant(): void
    {
        $config = new WechatConfig($this->validConfig, 'custom_tenant');

        self::assertSame('custom_tenant', $config->getTenant());
    }

    public function testConstructWithoutNotifyUrl(): void
    {
        $config = new WechatConfig([
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
        ]);

        self::assertSame('', $config->getNotifyUrl());
    }

    public function testConstructMissingRequired(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 缺少微信配置 -- [mch_id]');

        new WechatConfig([
            // missing mch_id
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ]);
    }

    public function testConstructInvalidSecretKeyLength(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: mch_secret_key 长度应为 32 字节');

        new WechatConfig([
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => 'short_key', // 9 bytes, not 32
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ]);
    }

    public function testConstructServiceModeMissingSubMchId(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 服务商模式下缺少 [sub_mch_id]');

        new WechatConfig([
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
            'mode' => Pay::MODE_SERVICE,
            // missing sub_mch_id
        ]);
    }

    public function testConstructServiceModeWithSubMchId(): void
    {
        $config = new WechatConfig([
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
            'mode' => Pay::MODE_SERVICE,
            'sub_mch_id' => 'sub_mch_id',
        ]);

        self::assertSame(Pay::MODE_SERVICE, $config->getMode());
        self::assertSame('sub_mch_id', $config->getSubMchId());
    }

    public function testOptionalGetters(): void
    {
        $config = new WechatConfig(array_merge($this->validConfig, [
            'mp_app_id' => 'mp_app_id',
            'mini_app_id' => 'mini_app_id',
            'app_id' => 'app_id',
            'mch_secret_key_v2' => 'v2_key',
            'sub_mp_app_id' => 'sub_mp_app_id',
            'sub_mini_app_id' => 'sub_mini_app_id',
            'sub_app_id' => 'sub_app_id',
        ]));

        self::assertSame('mp_app_id', $config->getMpAppId());
        self::assertSame('mini_app_id', $config->getMiniAppId());
        self::assertSame('app_id', $config->getAppId());
        self::assertSame('v2_key', $config->getMchSecretKeyV2());
        self::assertSame('sub_mp_app_id', $config->getSubMpAppId());
        self::assertSame('sub_mini_app_id', $config->getSubMiniAppId());
        self::assertSame('sub_app_id', $config->getSubAppId());
    }

    public function testOptionalGettersNull(): void
    {
        $config = new WechatConfig($this->validConfig);

        self::assertNull($config->getMpAppId());
        self::assertNull($config->getMiniAppId());
        self::assertNull($config->getAppId());
        self::assertNull($config->getMchSecretKeyV2());
        self::assertNull($config->getSubMpAppId());
        self::assertNull($config->getSubMiniAppId());
        self::assertNull($config->getSubAppId());
        self::assertNull($config->getSubMchId());
    }

    public function testValidateForV2(): void
    {
        $this->expectNotToPerformAssertions();

        $config = new WechatConfig(array_merge($this->validConfig, [
            'mch_secret_key_v2' => 'v2_key',
        ]));

        $config->validateForV2();
    }

    public function testValidateForV2Missing(): void
    {
        $config = new WechatConfig($this->validConfig);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 缺少微信配置 -- [mch_secret_key_v2]');

        $config->validateForV2();
    }

    public function testValidateForMp(): void
    {
        $this->expectNotToPerformAssertions();

        $config = new WechatConfig(array_merge($this->validConfig, [
            'mp_app_id' => 'mp_app_id',
        ]));

        $config->validateForMp();
    }

    public function testValidateForMpMissing(): void
    {
        $config = new WechatConfig($this->validConfig);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 缺少微信配置 -- [mp_app_id]');

        $config->validateForMp();
    }

    public function testValidateForMini(): void
    {
        $this->expectNotToPerformAssertions();

        $config = new WechatConfig(array_merge($this->validConfig, [
            'mini_app_id' => 'mini_app_id',
        ]));

        $config->validateForMini();
    }

    public function testValidateForMiniMissing(): void
    {
        $config = new WechatConfig($this->validConfig);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 缺少微信配置 -- [mini_app_id]');

        $config->validateForMini();
    }

    public function testWechatConfigInitImportsToCertManager(): void
    {
        CertManager::clearCache();

        $tenant = 'test-import';
        $config = new WechatConfig([
            'mch_id' => 'test-mch',
            'mch_secret_key' => str_repeat('a', 32),
            'mch_secret_cert' => 'test-cert',
            'mch_public_cert_path' => 'test-path',
            'wechat_public_cert_path' => [
                'SERIAL1' => 'cert-content-1',
                'SERIAL2' => 'cert-content-2',
            ],
        ], $tenant);

        self::assertEquals('cert-content-1', CertManager::wechatGetCertBySerial($tenant, 'SERIAL1'));
        self::assertEquals('cert-content-2', CertManager::wechatGetCertBySerial($tenant, 'SERIAL2'));
    }
}
