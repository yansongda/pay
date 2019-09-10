<?php

namespace Yansongda\Pay\Tests;

use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Yansongda\Pay\Exception\UnknownServiceException;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Config;
use Yansongda\Supports\Logger;

class PayTest extends TestCase
{
    protected $baseConfig = [
        'http' => [
            'timeout' => 5.0,
            'connect_timeout' => 3.0,
        ],
        'mode' => 'normal'
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
        $this->assertEquals(new Config(array_merge($this->baseConfig, $config)), $pay->config);
        $this->assertEquals(new Config(array_merge($this->baseConfig, $config)), $pay->get('config'));
        $this->assertInstanceOf(Logger::class, $pay->logger);
        $this->assertInstanceOf(Logger::class, $pay->log);
        $this->assertInstanceOf(EventDispatcher::class, $pay->event);

        $this->expectException(UnknownServiceException::class);
        $pay->get('foo');
    }

    public function testMagicSetAndSet()
    {
        $pay = new Pay([]);

        $pay->name = 'yansongda';
        $pay->set('age', '26');

        $this->assertEquals('yansongda', $pay->name);
        $this->assertEquals('yansongda', $pay->get('name'));
        $this->assertEquals('26', $pay->age);
        $this->assertEquals('26', $pay->get('age'));
    }

    public function testStaticCall()
    {
        $config = [];

        $this->assertInstanceOf(Config::class, Pay::config($config));
        $this->assertInstanceOf(Logger::class, Pay::logger($config));
        $this->assertInstanceOf(Logger::class, Pay::log($config));
        $this->assertInstanceOf(EventDispatcher::class, Pay::event($config));
    }

    public function testGetConfig()
    {
        $config = ['name' => 'yansongda'];

        $pay = new Pay($config);

        $this->assertArrayHasKey('name', $pay->getConfig());
        $this->assertEquals(array_merge($this->baseConfig, $config), $pay->getConfig());
    }
}