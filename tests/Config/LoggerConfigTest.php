<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Pay\Config\LoggerConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class LoggerConfigTest extends TestCase
{
    public function testConstructDefaults(): void
    {
        $config = new LoggerConfig([]);

        self::assertSame('default', $config->getTenant());
        self::assertFalse($config->isEnable());
        self::assertFalse($config->getEnable());
        self::assertSame('./logs/pay.log', $config->getFile());
        self::assertSame('info', $config->getLevel());
        self::assertSame('single', $config->getType());
        self::assertSame(30, $config->getMaxFile());
        self::assertSame(Pay::MODE_NORMAL, $config->getMode());
    }

    public function testConstructWithTenant(): void
    {
        $config = new LoggerConfig([], 'custom_tenant');

        self::assertSame('custom_tenant', $config->getTenant());
    }

    public function testConstructWithCustomValues(): void
    {
        $config = new LoggerConfig([
            'enable' => true,
            'file' => '/tmp/pay.log',
            'level' => 'debug',
            'type' => 'daily',
            'max_file' => 7,
        ]);

        self::assertTrue($config->isEnable());
        self::assertTrue($config->getEnable());
        self::assertSame('/tmp/pay.log', $config->getFile());
        self::assertSame('debug', $config->getLevel());
        self::assertSame('daily', $config->getType());
        self::assertSame(7, $config->getMaxFile());
    }
}
