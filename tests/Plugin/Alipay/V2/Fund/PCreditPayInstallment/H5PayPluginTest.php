<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Fund\PCreditPayInstallment;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\PCreditPayInstallment\H5PayPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;

class H5PayPluginTest extends TestCase
{
    protected H5PayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new H5PayPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.wap.pay', $result->getPayload()->toJson());
        self::assertStringContainsString('QUICK_WAP_WAY', $result->getPayload()->toJson());
    }
}
