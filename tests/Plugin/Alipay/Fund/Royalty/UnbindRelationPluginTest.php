<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Fund\Royalty;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\Fund\Royalty\UnbindRelationPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class UnbindRelationPluginTest extends TestCase
{
    protected UnbindRelationPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new UnbindRelationPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload()->toJson();

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.royalty.relation.unbind', $payload);
    }
}
