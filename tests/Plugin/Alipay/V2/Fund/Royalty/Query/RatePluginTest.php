<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Fund\Royalty\Query;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\Royalty\Query\RatePlugin;
use Yansongda\Pay\Tests\TestCase;

class RatePluginTest extends TestCase
{
    protected RatePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RatePlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload()->toJson();

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.royalty.rate.query', $payload);
    }
}
