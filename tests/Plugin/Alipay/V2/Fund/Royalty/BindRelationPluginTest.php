<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Fund\Royalty;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\Royalty\BindRelationPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;

class BindRelationPluginTest extends TestCase
{
    protected BindRelationPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new BindRelationPlugin();
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
