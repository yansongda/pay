<?php

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class AlipayConfigTest extends TestCase
{
    public function testConstruct()
    {
        $config = new AlipayConfig(
            app_id: '2016082000295641',
            app_secret_cert: 'test_secret',
            app_public_cert_path: '/path/to/app_public.crt',
            alipay_public_cert_path: '/path/to/alipay_public.crt',
            alipay_root_cert_path: '/path/to/alipay_root.crt',
            return_url: 'https://example.com/return',
            notify_url: 'https://example.com/notify',
        );

        self::assertEquals('2016082000295641', $config->app_id);
        self::assertEquals('test_secret', $config->app_secret_cert);
        self::assertEquals('/path/to/app_public.crt', $config->app_public_cert_path);
        self::assertEquals('/path/to/alipay_public.crt', $config->alipay_public_cert_path);
        self::assertEquals('/path/to/alipay_root.crt', $config->alipay_root_cert_path);
        self::assertEquals('https://example.com/return', $config->return_url);
        self::assertEquals('https://example.com/notify', $config->notify_url);
        self::assertEquals(Pay::MODE_NORMAL, $config->mode);
    }

    public function testToArray()
    {
        $config = new AlipayConfig(
            app_id: '2016082000295641',
            app_secret_cert: 'test_secret',
            app_public_cert_path: '/path/to/app_public.crt',
            alipay_public_cert_path: '/path/to/alipay_public.crt',
            alipay_root_cert_path: '/path/to/alipay_root.crt',
            return_url: 'https://example.com/return',
            notify_url: 'https://example.com/notify',
            mode: Pay::MODE_SANDBOX,
        );

        $array = $config->toArray();

        self::assertIsArray($array);
        self::assertEquals('2016082000295641', $array['app_id']);
        self::assertEquals('test_secret', $array['app_secret_cert']);
        self::assertEquals('/path/to/app_public.crt', $array['app_public_cert_path']);
        self::assertEquals('/path/to/alipay_public.crt', $array['alipay_public_cert_path']);
        self::assertEquals('/path/to/alipay_root.crt', $array['alipay_root_cert_path']);
        self::assertEquals('https://example.com/return', $array['return_url']);
        self::assertEquals('https://example.com/notify', $array['notify_url']);
        self::assertEquals(Pay::MODE_SANDBOX, $array['mode']);
    }

    public function testFromArray()
    {
        $array = [
            'app_id' => '2016082000295641',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => '/path/to/app_public.crt',
            'alipay_public_cert_path' => '/path/to/alipay_public.crt',
            'alipay_root_cert_path' => '/path/to/alipay_root.crt',
            'return_url' => 'https://example.com/return',
            'notify_url' => 'https://example.com/notify',
            'mode' => Pay::MODE_SERVICE,
        ];

        $config = AlipayConfig::fromArray($array);

        self::assertInstanceOf(AlipayConfig::class, $config);
        self::assertEquals('2016082000295641', $config->app_id);
        self::assertEquals('test_secret', $config->app_secret_cert);
        self::assertEquals('/path/to/app_public.crt', $config->app_public_cert_path);
        self::assertEquals('/path/to/alipay_public.crt', $config->alipay_public_cert_path);
        self::assertEquals('/path/to/alipay_root.crt', $config->alipay_root_cert_path);
        self::assertEquals('https://example.com/return', $config->return_url);
        self::assertEquals('https://example.com/notify', $config->notify_url);
        self::assertEquals(Pay::MODE_SERVICE, $config->mode);
    }

    public function testFromArrayWithDefaults()
    {
        $array = [
            'app_id' => '2016082000295641',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => '/path/to/app_public.crt',
            'alipay_public_cert_path' => '/path/to/alipay_public.crt',
            'alipay_root_cert_path' => '/path/to/alipay_root.crt',
        ];

        $config = AlipayConfig::fromArray($array);

        self::assertEquals('', $config->return_url);
        self::assertEquals('', $config->notify_url);
        self::assertEquals('', $config->app_auth_token);
        self::assertEquals('', $config->service_provider_id);
        self::assertEquals(Pay::MODE_NORMAL, $config->mode);
    }
}
