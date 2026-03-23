<?php

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Config\Config;
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Config\HttpConfig;
use Yansongda\Pay\Config\JsbConfig;
use Yansongda\Pay\Config\LoggerConfig;
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class ConfigTest extends TestCase
{
    public function testConstruct()
    {
        $alipayConfig = new AlipayConfig(
            app_id: '2016082000295641',
            app_secret_cert: 'test_secret',
            app_public_cert_path: '/path/to/app_public.crt',
            alipay_public_cert_path: '/path/to/alipay_public.crt',
            alipay_root_cert_path: '/path/to/alipay_root.crt',
        );

        $loggerConfig = new LoggerConfig(
            enable: true,
            file: './logs/test.log',
            level: 'debug',
        );

        $config = new Config(
            alipay: $alipayConfig,
            logger: $loggerConfig,
        );

        self::assertInstanceOf(AlipayConfig::class, $config->alipay);
        self::assertInstanceOf(LoggerConfig::class, $config->logger);
        self::assertNull($config->wechat);
        self::assertNull($config->unipay);
    }

    public function testToArray()
    {
        $alipayConfig = new AlipayConfig(
            app_id: '2016082000295641',
            app_secret_cert: 'test_secret',
            app_public_cert_path: '/path/to/app_public.crt',
            alipay_public_cert_path: '/path/to/alipay_public.crt',
            alipay_root_cert_path: '/path/to/alipay_root.crt',
        );

        $wechatConfig = new WechatConfig(
            mch_id: '1234567890',
            mch_secret_key: 'secret_key',
            mch_secret_cert: 'secret_cert',
            mch_public_cert_path: '/path/to/cert.pem',
            notify_url: 'https://example.com/notify',
        );

        $loggerConfig = new LoggerConfig(
            enable: true,
        );

        $httpConfig = new HttpConfig(
            timeout: 10.0,
        );

        $config = new Config(
            alipay: $alipayConfig,
            wechat: $wechatConfig,
            logger: $loggerConfig,
            http: $httpConfig,
        );

        $array = $config->toArray();

        self::assertIsArray($array);
        self::assertArrayHasKey('alipay', $array);
        self::assertArrayHasKey('default', $array['alipay']);
        self::assertEquals('2016082000295641', $array['alipay']['default']['app_id']);

        self::assertArrayHasKey('wechat', $array);
        self::assertArrayHasKey('default', $array['wechat']);
        self::assertEquals('1234567890', $array['wechat']['default']['mch_id']);

        self::assertArrayHasKey('logger', $array);
        self::assertTrue($array['logger']['enable']);

        self::assertArrayHasKey('http', $array);
        self::assertEquals(10.0, $array['http']['timeout']);
    }

    public function testFromArray()
    {
        $array = [
            'alipay' => [
                'default' => [
                    'app_id' => '2016082000295641',
                    'app_secret_cert' => 'test_secret',
                    'app_public_cert_path' => '/path/to/app_public.crt',
                    'alipay_public_cert_path' => '/path/to/alipay_public.crt',
                    'alipay_root_cert_path' => '/path/to/alipay_root.crt',
                ],
            ],
            'wechat' => [
                'default' => [
                    'mch_id' => '1234567890',
                    'mch_secret_key' => 'secret_key',
                    'mch_secret_cert' => 'secret_cert',
                    'mch_public_cert_path' => '/path/to/cert.pem',
                    'notify_url' => 'https://example.com/notify',
                ],
            ],
            'logger' => [
                'enable' => true,
                'level' => 'debug',
            ],
            'http' => [
                'timeout' => 10.0,
            ],
        ];

        $config = Config::fromArray($array);

        self::assertInstanceOf(Config::class, $config);
        self::assertInstanceOf(AlipayConfig::class, $config->alipay);
        self::assertEquals('2016082000295641', $config->alipay->app_id);

        self::assertInstanceOf(WechatConfig::class, $config->wechat);
        self::assertEquals('1234567890', $config->wechat->mch_id);

        self::assertInstanceOf(LoggerConfig::class, $config->logger);
        self::assertTrue($config->logger->enable);
        self::assertEquals('debug', $config->logger->level);

        self::assertInstanceOf(HttpConfig::class, $config->http);
        self::assertEquals(10.0, $config->http->timeout);
    }

    public function testFromArrayWithMultipleConfigs()
    {
        // Test with multiple config keys (like multiple tenants)
        $array = [
            'alipay' => [
                'tenant1' => [
                    'app_id' => '2016082000295641',
                    'app_secret_cert' => 'test_secret',
                    'app_public_cert_path' => '/path/to/app_public.crt',
                    'alipay_public_cert_path' => '/path/to/alipay_public.crt',
                    'alipay_root_cert_path' => '/path/to/alipay_root.crt',
                ],
            ],
        ];

        $config = Config::fromArray($array);

        self::assertInstanceOf(Config::class, $config);
        self::assertInstanceOf(AlipayConfig::class, $config->alipay);
        self::assertEquals('2016082000295641', $config->alipay->app_id);
    }

    public function testToArrayAndFromArrayRoundTrip()
    {
        $originalConfig = new Config(
            alipay: new AlipayConfig(
                app_id: '2016082000295641',
                app_secret_cert: 'test_secret',
                app_public_cert_path: '/path/to/app_public.crt',
                alipay_public_cert_path: '/path/to/alipay_public.crt',
                alipay_root_cert_path: '/path/to/alipay_root.crt',
                mode: Pay::MODE_SANDBOX,
            ),
            logger: new LoggerConfig(
                enable: true,
                level: 'debug',
            ),
        );

        $array = $originalConfig->toArray();
        $newConfig = Config::fromArray($array);

        self::assertEquals($originalConfig->alipay->app_id, $newConfig->alipay->app_id);
        self::assertEquals($originalConfig->alipay->mode, $newConfig->alipay->mode);
        self::assertEquals($originalConfig->logger->enable, $newConfig->logger->enable);
        self::assertEquals($originalConfig->logger->level, $newConfig->logger->level);
    }
}
