<?php

namespace Yansongda\Pay\Tests;

use DI\Container;
use DI\ContainerBuilder;
use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Contract\EventDispatcherInterface;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Contract\LoggerInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Tests\Stubs\FooServiceProviderStub;
use Yansongda\Supports\Config;
use Yansongda\Supports\Logger;
use Yansongda\Supports\Pipeline;

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

        // force
        $result1 = Pay::config(['name' => 'yansongda1', '_force' => true]);
        self::assertTrue($result1);
        self::assertEquals('yansongda1', Pay::get(ConfigInterface::class)->get('name'));

        // 直接使用 config 去设置 container
        if (class_exists(Container::class)) {
            // container - closure
            Pay::clear();
            $container2 = (new ContainerBuilder())->build();
            $result2 = Pay::config(['name' => 'yansongda2'], function () use ($container2) {
                return $container2;
            });
            self::assertTrue($result2);
            self::assertSame($container2, Pay::getContainer());

            // container - object
            Pay::clear();
            $container3 = (new ContainerBuilder())->build();
            $result3 = Pay::config(['name' => 'yansongda2'], $container3);
            self::assertTrue($result3);
            self::assertSame($container3, Pay::getContainer());

            // container - object force
            Pay::clear();
            $container4 = (new ContainerBuilder())->build();
            Pay::setContainer($container4);
            $result4 = Pay::config(['name' => 'yansongda2', '_force' => true]);
            self::assertTrue($result4);
            self::assertSame($container4, Pay::getContainer());
        }
    }

    public function testDirectCallStatic()
    {
        $pay = Pay::alipay([]);
        self::assertInstanceOf(Alipay::class, $pay);

        if (class_exists(Container::class)) {
            Pay::clear();
            $container3 = (new ContainerBuilder())->build();
            $pay = Pay::alipay([], $container3);

            self::assertInstanceOf(Alipay::class, $pay);
        }
    }

    public function testSetAndGet()
    {
        Pay::config(['name' => 'yansongda']);

        Pay::set('age', 28);

        self::assertEquals(28, Pay::get('age'));
    }

    public function testHas()
    {
        Pay::config(['name' => 'yansongda']);

        Pay::set('age', 28);

        self::assertFalse(Pay::has('name'));
        self::assertTrue(Pay::has('age'));
    }

    public function testGetContainerAndClear()
    {
        Pay::config(['name' => 'yansongda']);
        self::assertInstanceOf(ContainerInterface::class, Pay::getContainer());

        Pay::clear();

        $this->expectException(ContainerException::class);
        $this->expectExceptionCode(Exception::CONTAINER_NOT_FOUND);
        $this->expectExceptionMessage('`getContainer()` failed! Maybe you should `setContainer()` first');

        Pay::getContainer();
    }

    public function testMakeService()
    {
        Pay::config(['name' => 'yansongda']);
        self::assertNotSame(Pay::make(Pipeline::class), Pay::make(Pipeline::class));
    }

    public function testRegisterService()
    {
        Pay::config(['name' => 'yansongda']);

        Pay::registerService(FooServiceProviderStub::class, []);

        self::assertEquals('bar', Pay::get('foo'));
    }

    public function testMagicCallNotFoundService()
    {
        $this->expectException(ServiceNotFoundException::class);

        Pay::foo1([]);
    }

    public function testCoreServiceContainer()
    {
        Pay::config(['name' => 'yansongda']);

        // 单在 hyperf 框架内没有 container，所以手动设置一个
        if (class_exists(Container::class) && class_exists(ApplicationContext::class)) {
            ApplicationContext::setContainer((new ContainerBuilder())->build());
        }

        self::assertInstanceOf(ContainerInterface::class, Pay::getContainer());
    }

    public function testCoreServiceConfig()
    {
        $config = ['name' => 'yansongda'];
        Pay::config($config);

        self::assertInstanceOf(Config::class, Pay::get(ConfigInterface::class));
        self::assertEquals($config['name'], Pay::get(ConfigInterface::class)->get('name'));

        // 修改 config 的情况
        $config2 = [
            'name' => 'yansongda2',
        ];
        Pay::set(ConfigInterface::class, new Config($config2));

        self::assertEquals($config2['name'], Pay::get(ConfigInterface::class)->get('name'));
    }

    public function testCoreServiceLogger()
    {
        $config = ['name' => 'yansongda','logger' => ['enable' => true]];
        Pay::config($config);

        self::assertInstanceOf(Logger::class, Pay::get(LoggerInterface::class));

        $otherLogger = new \Monolog\Logger('test');
        Pay::set(LoggerInterface::class, $otherLogger);
        self::assertEquals($otherLogger, Pay::get(LoggerInterface::class));
    }

    public function testCoreServiceEvent()
    {
        $config = ['name' => 'yansongda'];
        Pay::config($config);

        self::assertInstanceOf(EventDispatcher::class, Pay::get(EventDispatcherInterface::class));
    }

    public function testCoreServiceHttpClient()
    {
        $config = ['name' => 'yansongda'];
        Pay::config($config);

        self::assertInstanceOf(Client::class, Pay::get(HttpClientInterface::class));

        // 使用外部 http client
        $oldClient = Pay::get(HttpClientInterface::class);

        $client = new Client(['timeout' => 3.0]);
        Pay::set(HttpClientInterface::class, $client);

        self::assertEquals($client, Pay::get(HttpClientInterface::class));
        self::assertNotEquals($oldClient, Pay::get(HttpClientInterface::class));
    }
}
