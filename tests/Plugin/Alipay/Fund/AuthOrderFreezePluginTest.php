<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Fund;

use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Plugin\Alipay\Fund\AuthOrderFreezePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class AuthOrderFreezePluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $plugin = new AuthOrderFreezePlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseParser::class, $result->getDirection());
        self::assertStringContainsString('alipay.fund.auth.order.freeze', $result->getPayload()->toJson());
        self::assertStringContainsString('PRE_AUTH', $result->getPayload()->toJson());
    }
}
