<?php

namespace Yansongda\Pay\Tests;

use Hyperf\Pimple\ContainerFactory;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
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
}
