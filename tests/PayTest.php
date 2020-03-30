<?php

namespace Yansongda\Pay\Tests;

use DI\Container;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Contract\ContainerInterface;
use Yansongda\Pay\Contract\EventDispatcherInterface;
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

        $this->assertEquals($data, Pay::get('profile'));
    }

    public function testBase()
    {
        $container = Pay::getContainer([]);

        $this->assertInstanceOf(Container::class, $container);
        $this->assertInstanceOf(Container::class, $container->get(ContainerInterface::class));
        $this->assertInstanceOf(Pay::class, Pay::get(Pay::class));
    }

    public function testConfig()
    {
        $config = [
            'name' => 'yansongda',
        ];

        $container = Pay::getContainer($config);

        $this->assertInstanceOf(Container::class, $container);
        $this->assertInstanceOf(Config::class, $container->get(ConfigInterface::class));
        $this->assertEquals($config['name'], Pay::get(ConfigInterface::class)->get('name'));

        // 修改 config 的情况
        $config2 = [
            'name' => 'yansongda2',
        ];
        Pay::set(ConfigInterface::class, new Config($config2));

        $this->assertEquals($config2['name'], Pay::get(ConfigInterface::class)->get('name'));
    }

    public function testLogger()
    {
        $container = Pay::getContainer([]);
        $otherLogger = new \Monolog\Logger('test');

        $this->assertInstanceOf(Logger::class, $container->get(\Yansongda\Pay\Contract\LoggerInterface::class));
        $this->assertInstanceOf(\Monolog\Logger::class, $container->get(\Yansongda\Pay\Contract\LoggerInterface::class)->getLogger());
        $this->assertInstanceOf(LoggerInterface::class, $container->get(\Yansongda\Pay\Contract\LoggerInterface::class)->getLogger());
        $this->assertNotEquals($otherLogger, $container->get(\Yansongda\Pay\Contract\LoggerInterface::class));

        $container->get(\Yansongda\Pay\Contract\LoggerInterface::class)->setLogger($otherLogger);
        $this->assertEquals($otherLogger, $container->get(\Yansongda\Pay\Contract\LoggerInterface::class)->getLogger());
    }

    public function testEvent()
    {
        $container = Pay::getContainer([]);

        $this->assertInstanceOf(EventDispatcher::class, $container->get(EventDispatcherInterface::class));
    }

    public function testSingletonContainer()
    {
        $config1 = ['name' => 'yansongda'];
        $config2 = ['age' => 26];

        $container1 = Pay::getContainer($config1);
        $container2 = Pay::getContainer($config2);

        $this->assertEquals($container1, $container2);
    }

    public function testGetContainer()
    {
        $this->expectExceptionMessage('You Must Init The Container First');
        $this->expectException(ContainerNotFoundException::class);

        Pay::getContainer();
    }

    public function testGetForceContainer()
    {
        $this->assertInstanceOf(Container::class, Pay::getContainer([]));
    }
}