<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Pay\Web;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\Pay\Web\PayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class PayPluginTest extends TestCase
{
    protected PayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PayPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.page.pay', $result->getPayload()->toJson());
        self::assertStringContainsString('FAST_INSTANT_TRADE_PAY', $result->getPayload()->toJson());
    }
}
