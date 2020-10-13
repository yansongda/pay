<?php

namespace Yansongda\Pay\Tests;

use DI\Container;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Contract\ContainerInterface;
use Yansongda\Pay\Contract\EventDispatcherInterface;
use Yansongda\Pay\Contract\HttpInterface;
use Yansongda\Pay\Exception\ContainerNotFoundException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Config;
use Yansongda\Supports\Logger;

class PayTest extends TestCase
{
    public function testMagicCallNotFoundService()
    {
        $this->expectException(ServiceNotFoundException::class);

        Pay::foo([]);
    }

    public function testSetAndGet()
    {
        $data = [
            'name' => 'yansongda',
            'age' => 26
        ];

        Pay::getContainer([]);

        Pay::set('profile', $data);

        self::assertEquals($data, Pay::get('profile'));
    }

    public function testCoreServiceBase()
    {
        $container = Pay::getContainer([]);

        self::assertInstanceOf(Container::class, $container);
        self::assertInstanceOf(Pay::class, $container->get(ContainerInterface::class));
        self::assertInstanceOf(Pay::class, Pay::get(Pay::class));
    }

    public function testCoreServiceConfig()
    {
        $config = [
            'name' => 'yansongda',
        ];

        $container = Pay::getContainer($config);

        self::assertInstanceOf(Container::class, $container);
        self::assertInstanceOf(Config::class, $container->get(ConfigInterface::class));
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
        $container = Pay::getContainer([]);
        $otherLogger = new \Monolog\Logger('test');

        self::assertInstanceOf(Logger::class, $container->get(\Yansongda\Pay\Contract\LoggerInterface::class));
        self::assertInstanceOf(\Monolog\Logger::class, $container->get(\Yansongda\Pay\Contract\LoggerInterface::class)->getLogger());
        self::assertInstanceOf(LoggerInterface::class, $container->get(\Yansongda\Pay\Contract\LoggerInterface::class)->getLogger());
        self::assertNotEquals($otherLogger, $container->get(\Yansongda\Pay\Contract\LoggerInterface::class));

        $container->get(\Yansongda\Pay\Contract\LoggerInterface::class)->setLogger($otherLogger);
        self::assertEquals($otherLogger, $container->get(\Yansongda\Pay\Contract\LoggerInterface::class)->getLogger());
    }

    public function testCoreServiceEvent()
    {
        $container = Pay::getContainer([]);

        self::assertInstanceOf(EventDispatcher::class, $container->get(EventDispatcherInterface::class));
    }

    public function testCoreServiceHttpClient()
    {
        $container = Pay::getContainer([]);

        self::assertInstanceOf(Client::class, $container->get(HttpInterface::class));
    }

    public function testCoreServiceExternalHttpClient()
    {
        Pay::getContainer([]);

        $client = new Client(['timeout' => 3.0]);

        Pay::set(HttpInterface::class, $client);

        self::assertEquals($client, Pay::get(HttpInterface::class));
    }

    public function testSingletonContainer()
    {
        $config1 = ['name' => 'yansongda'];
        $config2 = ['age' => 26];

        $container1 = Pay::getContainer($config1);
        $container2 = Pay::getContainer($config2);

        self::assertEquals($container1, $container2);
    }

    public function testGetNotFoundContainer()
    {
        $this->expectExceptionMessage('You must init the container first with config');
        $this->expectException(ContainerNotFoundException::class);

        Pay::getContainer();
    }

    public function testGetForceContainer()
    {
        self::assertInstanceOf(Container::class, Pay::getContainer([]));
    }
}
