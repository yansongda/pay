<?php

namespace Yansongda\Pay\Tests;

use Yansongda\Pay\Contracts\GatewayApplicationInterface;
use Yansongda\Pay\Exceptions\InvalidGatewayException;
use Yansongda\Pay\Gateways\Alipay;
use Yansongda\Pay\Gateways\Wechat;
use Yansongda\Pay\Pay;

class PayTest extends TestCase
{
    public function testAlipayGateway()
    {
        $alipay = Pay::alipay(['foo' => 'bar']);

        $this->assertInstanceOf(Alipay::class, $alipay);
        $this->assertInstanceOf(GatewayApplicationInterface::class, $alipay);
    }

    public function testWechatGateway()
    {
        $wechat = Pay::wechat(['foo' => 'bar']);

        $this->assertInstanceOf(Wechat::class, $wechat);
        $this->assertInstanceOf(GatewayApplicationInterface::class, $wechat);
    }

    public function testFooGateway()
    {
        $this->expectException(InvalidGatewayException::class);
        $this->expectExceptionMessage('INVALID_GATEWAY: Gateway [foo] Not Exists');

        Pay::foo([]);
    }
}
