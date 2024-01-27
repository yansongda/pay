<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Fund\Royalty\Relation;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\Royalty\Relation\BindPlugin;
use Yansongda\Pay\Tests\TestCase;

class BindPluginTest extends TestCase
{
    protected BindPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new BindPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload()->toJson();

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.royalty.relation.bind', $payload);
    }
}
