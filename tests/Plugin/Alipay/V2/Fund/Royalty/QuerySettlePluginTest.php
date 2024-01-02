<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Fund\Royalty;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\Royalty\QuerySettlePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class QuerySettlePluginTest extends TestCase
{
    protected QuerySettlePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QuerySettlePlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload()->toJson();

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.order.settle.query', $payload);
    }
}
