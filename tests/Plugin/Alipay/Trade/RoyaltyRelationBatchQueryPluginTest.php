<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Trade;

use Yansongda\Pay\Plugin\Alipay\Trade\RoyaltyRelationBatchQueryPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class RoyaltyRelationBatchQueryPluginTest extends TestCase
{
    protected RoyaltyRelationBatchQueryPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RoyaltyRelationBatchQueryPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertStringContainsString('alipay.trade.royalty.relation.batchquery', $result->getPayload()->toJson());
    }
}
