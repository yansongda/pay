<?php

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class WechatConfigTest extends TestCase
{
    public function testConstruct()
    {
        $config = new WechatConfig(
            mch_id: '1234567890',
            mch_secret_key: 'secret_key',
            mch_secret_cert: 'secret_cert',
            mch_public_cert_path: '/path/to/cert.pem',
            notify_url: 'https://example.com/notify',
            mp_app_id: 'wx1234567890',
        );

        self::assertEquals('1234567890', $config->mch_id);
        self::assertEquals('secret_key', $config->mch_secret_key);
        self::assertEquals('secret_cert', $config->mch_secret_cert);
        self::assertEquals('/path/to/cert.pem', $config->mch_public_cert_path);
        self::assertEquals('https://example.com/notify', $config->notify_url);
        self::assertEquals('wx1234567890', $config->mp_app_id);
    }

    public function testToArray()
    {
        $config = new WechatConfig(
            mch_id: '1234567890',
            mch_secret_key: 'secret_key',
            mch_secret_cert: 'secret_cert',
            mch_public_cert_path: '/path/to/cert.pem',
            notify_url: 'https://example.com/notify',
            wechat_public_cert_path: ['serial' => '/path/to/wechat.pem'],
        );

        $array = $config->toArray();

        self::assertIsArray($array);
        self::assertEquals('1234567890', $array['mch_id']);
        self::assertEquals('secret_key', $array['mch_secret_key']);
        self::assertEquals('secret_cert', $array['mch_secret_cert']);
        self::assertEquals('/path/to/cert.pem', $array['mch_public_cert_path']);
        self::assertEquals('https://example.com/notify', $array['notify_url']);
        self::assertIsArray($array['wechat_public_cert_path']);
        self::assertEquals('/path/to/wechat.pem', $array['wechat_public_cert_path']['serial']);
    }

    public function testFromArray()
    {
        $array = [
            'mch_id' => '1234567890',
            'mch_secret_key' => 'secret_key',
            'mch_secret_cert' => 'secret_cert',
            'mch_public_cert_path' => '/path/to/cert.pem',
            'notify_url' => 'https://example.com/notify',
            'mini_app_id' => 'wx_mini_123',
            'mode' => Pay::MODE_SERVICE,
        ];

        $config = WechatConfig::fromArray($array);

        self::assertInstanceOf(WechatConfig::class, $config);
        self::assertEquals('1234567890', $config->mch_id);
        self::assertEquals('secret_key', $config->mch_secret_key);
        self::assertEquals('wx_mini_123', $config->mini_app_id);
        self::assertEquals(Pay::MODE_SERVICE, $config->mode);
    }
}
