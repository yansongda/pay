<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Trade;

use Yansongda\Pay\Plugin\Alipay\Trade\RoyaltyRelationBatchQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Trade\RoyaltyRelationUnbindPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class RoyaltyRelationUnbindPluginTest extends TestCase
{
    protected RoyaltyRelationUnbindPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RoyaltyRelationUnbindPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertStringContainsString('alipay.trade.royalty.relation.unbind', $result->getPayload()->toJson());
    }
}
