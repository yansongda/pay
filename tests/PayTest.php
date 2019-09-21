<?php

namespace Yansongda\Pay\Tests;

use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Yansongda\Pay\Contract\ServiceInterface;
use Yansongda\Pay\Exception\UnknownServiceException;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Config;
use Yansongda\Supports\Logger;

class PayTest extends TestCase
{
    protected $baseConfig = [
        'log' => [
            'enable' => true,
            'file' => null,
            'identify' => 'yansongda.supports',
            'level' => 'debug',
            'type' => 'daily',
            'max_files' => 30
        ],
        'http' => [
            'timeout' => 5.0,
            'connect_timeout' => 3.0,
        ],
        'mode' => 'normal',
    ];

    public function testBootstrap()
    {
        $pay = new Pay([]);

        $this->assertInstanceOf(Pay::class, $pay);
        $this->assertInstanceOf(Container::class, $pay);
    }

    public function testMagicGetAndGet()
    {
        $config = [];

        $pay = new Pay($config);

        $this->assertInstanceOf(Config::class, $pay->config);
        $this->assertInstanceOf(ServiceInterface::class, $pay->config);

        $this->assertInstanceOf(Logger::class, $pay->logger);
        $this->assertInstanceOf(Logger::class, $pay->log);
        $this->assertInstanceOf(ServiceInterface::class, $pay->logger);

        $this->assertInstanceOf(EventDispatcher::class, $pay->event);
        $this->assertInstanceOf(ServiceInterface::class, $pay->event);

        // todo alipay && wechat

        $this->expectException(UnknownServiceException::class);
        $pay->get('foo');
    }

    public function testMagicSetAndSet()
    {
        $pay = new Pay([]);

        $test = new class implements ServiceInterface {
            public $name = 'yansongda';
            public $age = 26;
        };

        $pay->test = $test;
        $pay->set('test2', $test);

        $this->assertEquals($test, $pay->test);
        $this->assertEquals($test, $pay->test2);
        $this->assertEquals('yansongda', $pay->test->name);
        $this->assertEquals(26, $pay->test->age);
    }

    public function testStaticCall()
    {
        $config = [];

        $this->assertInstanceOf(Config::class, Pay::config($config));
        $this->assertInstanceOf(Logger::class, Pay::logger($config));
        $this->assertInstanceOf(Logger::class, Pay::log($config));
        $this->assertInstanceOf(EventDispatcher::class, Pay::event($config));

        $this->assertInstanceOf(ServiceInterface::class, Pay::config($config));
        $this->assertInstanceOf(ServiceInterface::class, Pay::logger($config));
        $this->assertInstanceOf(ServiceInterface::class, Pay::log($config));
        $this->assertInstanceOf(ServiceInterface::class, Pay::event($config));
    }

    public function testGetConfig()
    {
        $config = ['name' => 'yansongda'];

        $pay = new Pay($config);

        $this->assertArrayHasKey('name', $pay->getConfig());
        $this->assertEquals(array_replace_recursive($this->baseConfig, $config), $pay->getConfig());
    }
}