<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Trade;

use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Plugin\Alipay\Trade\PagePayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class PagePayPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $plugin = new PagePayPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals(ResponseParser::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.page.pay', $result->getPayload()->toJson());
        self::assertStringContainsString('FAST_INSTANT_TRADE_PAY', $result->getPayload()->toJson());
    }
}
