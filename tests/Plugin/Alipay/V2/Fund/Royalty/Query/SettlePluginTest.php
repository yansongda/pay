<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Fund\Royalty\Query;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\Royalty\Query\SettlePlugin;
use Yansongda\Pay\Tests\TestCase;

class SettlePluginTest extends TestCase
{
    protected SettlePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new SettlePlugin();
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
