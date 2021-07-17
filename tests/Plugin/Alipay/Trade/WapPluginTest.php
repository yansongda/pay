<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Trade;

use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Plugin\Alipay\Trade\WapPayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class WapPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $plugin = new WapPayPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals(ResponseParser::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.wap.pay', $result->getPayload()->toJson());
        self::assertStringContainsString('QUICK_WAP_PAY', $result->getPayload()->toJson());
    }
}
