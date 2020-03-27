<?php

namespace Yansongda\Pay\Tests;

use DI\Container;
use Psr\Log\LoggerInterface;
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
        $config = [];

        $container = Pay::container($config);

        $this->assertInstanceOf(Container::class, $container);
        $this->assertInstanceOf(Pay::class, Pay::get('pay'));
    }

    public function testConfig()
    {
        $config = [
            'name' => 'yansongda',
            'age' => 26
        ];

        $container = Pay::getContainer($config);

        $this->assertInstanceOf(Container::class, $container);
        $this->assertInstanceOf(Config::class, $container->get('config'));
        $this->assertInstanceOf(Config::class, Pay::get('config'));
        $this->assertEquals($config['name'], Pay::get('config')->get('name'));
    }

    public function testLogger()
    {
        $config = [];

        $container = Pay::container($config);

        $this->assertInstanceOf(Logger::class, $container->get('logger'));
        $this->assertInstanceOf(Logger::class, $container->get('log'));
        $this->assertInstanceOf(\Monolog\Logger::class, $container->get('logger')->getLogger());
        $this->assertInstanceOf(LoggerInterface::class, $container->get('logger')->getLogger());
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