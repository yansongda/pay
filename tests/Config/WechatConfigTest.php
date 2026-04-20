<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
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
            'app_id' => 'test_app_id',
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '32ByteSecretKeyForTesting12345',
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
        self::assertSame('32ByteSecretKeyForTesting12345', $config->getMchSecretKey());
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

    public function testConstructMissingRequired(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 缺少微信配置 -- [mch_id]');

        new WechatConfig([
            'app_id' => 'test_app_id',
            // missing mch_id
            'mch_secret_key' => '32ByteSecretKeyForTesting12345',
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
            'app_id' => 'test_app_id',
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
            'app_id' => 'test_app_id',
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '32ByteSecretKeyForTesting12345',
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
            'app_id' => 'test_app_id',
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '32ByteSecretKeyForTesting12345',
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
        $config = new WechatConfig(array_merge($this->validConfig, [
            'mch_secret_key_v2' => 'v2_key',
        ]));

        // 不应抛出异常
        $config->validateForV2();
        self::assertTrue(true);
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
        $config = new WechatConfig(array_merge($this->validConfig, [
            'mp_app_id' => 'mp_app_id',
        ]));

        $config->validateForMp();
        self::assertTrue(true);
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
        $config = new WechatConfig(array_merge($this->validConfig, [
            'mini_app_id' => 'mini_app_id',
        ]));

        $config->validateForMini();
        self::assertTrue(true);
    }

    public function testValidateForMiniMissing(): void
    {
        $config = new WechatConfig($this->validConfig);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('配置异常: 缺少微信配置 -- [mini_app_id]');

        $config->validateForMini();
    }

    public function testPublicKeyBySerial(): void
    {
        $config = new WechatConfig(array_merge($this->validConfig, [
            'wechat_public_cert_path' => [
                'ABC123' => '/path/to/cert.pem',
            ],
        ]));

        self::assertSame('/path/to/cert.pem', $config->getPublicKeyBySerial('ABC123'));
        self::assertNull($config->getPublicKeyBySerial('UNKNOWN'));
    }

    public function testAllPublicCerts(): void
    {
        $config = new WechatConfig(array_merge($this->validConfig, [
            'wechat_public_cert_path' => [
                'ABC123' => '/path/to/cert.pem',
            ],
        ]));

        $certs = $config->getAllPublicCerts();
        self::assertArrayHasKey('ABC123', $certs);
        self::assertSame('/path/to/cert.pem', $certs['ABC123']);
    }
}