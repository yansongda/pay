<?php

namespace Yansongda\Pay\Tests;

use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Pay;

class PayTest extends TestCase
{
    public function testDriverWithoutConfig()
    {
        $this->expectException(InvalidArgumentException::class);

        $pay = new Pay([]);
        $pay->driver('foo');
    }

    public function testDriver()
    {
        $pay = new Pay(['alipay' => ['app_id' => '']]);

        $this->assertInstanceOf(Pay::class, $pay->driver('alipay'));
    }

    public function testGatewayWithoutDriver()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver is not defined.');

        $pay = new Pay([]);
        $pay->gateway();
    }

    public function testInvalidGateway()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway [foo] is not supported.');

        $pay = new Pay(['alipay' => ['app_id' => '']]);
        $pay->driver('alipay')->gateway('foo');
    }

    public function testGateway()
    {
        $pay = new Pay(['alipay' => ['app_id' => '']]);
        $this->assertInstanceOf(GatewayInterface::class, $pay->driver('alipay')->gateway());
    }
}
