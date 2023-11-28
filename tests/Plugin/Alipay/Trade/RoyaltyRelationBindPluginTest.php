<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Trade;

use Yansongda\Pay\Plugin\Alipay\Trade\RoyaltyRelationBindPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class RoyaltyRelationBindPluginTest extends TestCase
{
    protected RoyaltyRelationBindPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RoyaltyRelationBindPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertStringContainsString('alipay.trade.royalty.relation.bind', $result->getPayload()->toJson());
    }
}
