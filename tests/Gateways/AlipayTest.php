<?php

namespace Yansongda\Pay\Tests\Gateways;

use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class AlipayTest extends TestCase
{
    public function testSuccess()
    {
        $success = Pay::alipay([])->success();

        $this->assertInstanceOf(Response::class, $success);
        $this->assertEquals('success', $success->getContent());
    }
}
