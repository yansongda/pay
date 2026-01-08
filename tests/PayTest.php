<?php

namespace Yansongda\Pay\Tests;

use Hyperf\Pimple\ContainerFactory;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Config\Config;
use Yansongda\Pay\Config\LoggerConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Provider\Jsb;
use Yansongda\Pay\Provider\Unipay;
use Yansongda\Pay\Provider\Wechat;

class PayTest extends TestCase
{
    protected function setUp(): void
    {
        Pay::clear();
    }

    protected function tearDown(): void
    {
        Pay::clear();
    }

    public function testConfig()
    {
        $result = Pay::config(['name' => 'yansongda']);
        self::assertTrue($result);
        self::assertEquals('yansongda', Pay::get(ConfigInterface::class)->get('name'));
        self::assertInstanceOf(Alipay::class, Pay::get('alipay'));
        self::assertInstanceOf(Alipay::class, Pay::get(Alipay::class));
        self::assertInstanceOf(Wechat::class, Pay::get('wechat'));
        self::assertInstanceOf(Wechat::class, Pay::get(Wechat::class));
        self::assertInstanceOf(Unipay::class, Pay::get('unipay'));
        self::assertInstanceOf(Unipay::class, Pay::get(Unipay::class));
        self::assertInstanceOf(Jsb::class, Pay::get('jsb'));
        self::assertInstanceOf(Jsb::class, Pay::get(Jsb::class));

        // force
        $result1 = Pay::config(['name' => 'yansongda1', '_force' => true]);
        self::assertTrue($result1);
        self::assertEquals('yansongda1', Pay::get(ConfigInterface::class)->get('name'));
    }

    public function testDirectCallStatic()
    {
        Pay::config();
        $pay = Pay::alipay();
        self::assertInstanceOf(Alipay::class, $pay);

        if (class_exists(ContainerFactory::class)) {
            Pay::clear();
            $container3 = (new ContainerFactory())();
            $pay = Pay::alipay([], $container3);

            self::assertSame($container3, Artful::getContainer());
            self::assertInstanceOf(Alipay::class, $pay);
        }
    }

    public function testSetAndGet()
    {
        Pay::config(['name' => 'yansongda']);

        Pay::set('age', 28);

        self::assertEquals(28, Pay::get('age'));
    }

    public function testMagicCallNotFoundService()
    {
        self::expectException(ServiceNotFoundException::class);

        Pay::foo1([]);
    }

    public function testConfigWithEntity()
    {
        $config = new Config(
            alipay: new AlipayConfig(
                app_id: '2016082000295641',
                app_secret_cert: 'test_secret',
                app_public_cert_path: '/path/to/app_public.crt',
                alipay_public_cert_path: '/path/to/alipay_public.crt',
                alipay_root_cert_path: '/path/to/alipay_root.crt',
            ),
            logger: new LoggerConfig(
                enable: true,
                level: 'debug',
            ),
        );

        $result = Pay::config($config);
        self::assertTrue($result);
        
        // Verify the config was properly converted to array and stored
        $storedConfig = Pay::get(ConfigInterface::class);
        self::assertArrayHasKey('alipay', $storedConfig->get('alipay'));
        self::assertEquals('2016082000295641', $storedConfig->get('alipay.default.app_id'));
        self::assertTrue($storedConfig->get('logger.enable'));
        self::assertEquals('debug', $storedConfig->get('logger.level'));
    }

    public function testConfigWithEntityForce()
    {
        $config1 = new Config(
            alipay: new AlipayConfig(
                app_id: '2016082000295641',
                app_secret_cert: 'test_secret',
                app_public_cert_path: '/path/to/app_public.crt',
                alipay_public_cert_path: '/path/to/alipay_public.crt',
                alipay_root_cert_path: '/path/to/alipay_root.crt',
            ),
        );

        Pay::config($config1);

        // Now force update with new config
        $config2 = new Config(
            alipay: new AlipayConfig(
                app_id: '9999999999999999',
                app_secret_cert: 'new_secret',
                app_public_cert_path: '/new/path/to/app_public.crt',
                alipay_public_cert_path: '/new/path/to/alipay_public.crt',
                alipay_root_cert_path: '/new/path/to/alipay_root.crt',
            ),
            additional: ['_force' => true],
        );

        $result = Pay::config($config2);
        self::assertTrue($result);

        $storedConfig = Pay::get(ConfigInterface::class);
        self::assertEquals('9999999999999999', $storedConfig->get('alipay.default.app_id'));
    }
}
