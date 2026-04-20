<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Config;

use Yansongda\Pay\Config\HttpConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class HttpConfigTest extends TestCase
{
    public function testConstructDefaults(): void
    {
        $config = new HttpConfig([]);

        self::assertSame('default', $config->getTenant());
        self::assertSame(5.0, $config->getTimeout());
        self::assertSame(5.0, $config->getConnectTimeout());
        self::assertSame(Pay::MODE_NORMAL, $config->getMode());
    }

    public function testConstructWithTenant(): void
    {
        $config = new HttpConfig([], 'custom_tenant');

        self::assertSame('custom_tenant', $config->getTenant());
    }

    public function testConstructWithCustomValues(): void
    {
        $config = new HttpConfig([
            'timeout' => 12.5,
            'connect_timeout' => 3.5,
        ]);

        self::assertSame(12.5, $config->getTimeout());
        self::assertSame(3.5, $config->getConnectTimeout());
    }
}
