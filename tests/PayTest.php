<?php

namespace Yansongda\Pay\Tests;

use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Yansongda\Pay\Contract\ServiceInterface;
use Yansongda\Pay\Exception\ServiceException;
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
            'identify' => 'yansongda.pay',
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

    public function testMagicSetAndSet()
    {
        $pay = new Pay([]);

        $test = new class implements ServiceInterface {
            public $name = 'yansongda';
            public $age = 26;
        };

        $pay->test = $test;
        $this->assertEquals($test, $pay->test);
        $this->assertEquals(26, $pay->test->age);
        $this->assertEquals('yansongda', $pay->test->name);

        $pay->set('test2', $test);
        $this->assertEquals($test, $pay->test2);
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
        $this->assertInstanceOf(ServiceInterface::class, $pay->log);

        $this->assertInstanceOf(EventDispatcher::class, $pay->event);
        $this->assertInstanceOf(ServiceInterface::class, $pay->event);

        // todo alipay && wechat
    }

    public function testMagicGetAndGetForUnknownServiceException()
    {
        $pay = new Pay([]);

        $this->expectException(UnknownServiceException::class);
        $pay->get('foo');
    }

    public function testMagicGetAndGetForServiceException()
    {
        $pay = new Pay([]);
        $pay->set('foo', '1');

        $this->expectException(ServiceException::class);
        $pay->get('foo');
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

        // todo alipay && wechat
    }

    public function testGetConfig()
    {
        $config = ['name' => 'yansongda'];
        $pay = new Pay($config);
        $this->assertEquals(array_replace_recursive($this->baseConfig, $config), $pay->getConfig());
        $this->assertArrayHasKey('name', $pay->getConfig());

        $config1 = ['http' => ['timeout' => '3']];
        $pay1 = new Pay($config1);
        $this->assertEquals(array_replace_recursive($this->baseConfig, $config1), $pay1->getConfig());
        $this->assertArrayHasKey('http', $pay1->getConfig());
        $this->assertArrayNotHasKey('name', $pay1->getConfig());
        $this->assertEquals('3', $pay1->getConfig()['http']['timeout']);
        $this->assertArrayHasKey('connect_timeout', $pay1->getConfig()['http']);
        $this->assertEquals('3', $pay1->getConfig()['http']['connect_timeout']);
    }
}