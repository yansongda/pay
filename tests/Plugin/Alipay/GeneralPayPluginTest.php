<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay;

use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\Stubs\Plugin\AlipayGeneralPluginStub;
use Yansongda\Pay\Tests\TestCase;

class GeneralPayPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $plugin = new AlipayGeneralPluginStub();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertStringContainsString('yansongda', $result->getPayload()->toJson());
    }
}

