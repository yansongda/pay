<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Trade;

use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Plugin\Alipay\Trade\PayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class PayPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $plugin = new PayPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseParser::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.pay', $result->getPayload()->toJson());
        self::assertStringContainsString('FACE_TO_FACE_PAYMENT', $result->getPayload()->toJson());
        self::assertStringContainsString('bar_code', $result->getPayload()->toJson());
    }
}
