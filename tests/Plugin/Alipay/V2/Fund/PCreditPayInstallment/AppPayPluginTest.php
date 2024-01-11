<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Fund\PCreditPayInstallment;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\PCreditPayInstallment\AppPayPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;

class AppPayPluginTest extends TestCase
{
    protected AppPayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AppPayPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.app.pay', $result->getPayload()->toJson());
    }
}
