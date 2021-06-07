<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Trade;

use PHPUnit\Framework\TestCase;
use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Plugin\Alipay\Trade\PageRefundPlugin;
use Yansongda\Pay\Rocket;

class PageRefundPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $plugin = new PageRefundPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals(ResponseParser::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.page.refund', $result->getPayload()->toJson());
    }
}
