<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Trade;

use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Plugin\Alipay\Trade\AppPayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class AppPayPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $plugin = new AppPayPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals(ResponseParser::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.app.pay', $result->getPayload()->toJson());
        self::assertStringContainsString('QUICK_MSECURITY_PAY', $result->getPayload()->toJson());
    }
}
