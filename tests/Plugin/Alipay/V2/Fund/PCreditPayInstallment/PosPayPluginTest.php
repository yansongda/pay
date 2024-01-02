<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Fund\PCreditPayInstallment;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\PCreditPayInstallment\PosPayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class PosPayPluginTest extends TestCase
{
    protected PosPayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PosPayPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload()->toJson();

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.pay', $payload);
        self::assertStringContainsString('FACE_TO_FACE_PAYMENT', $payload);
        self::assertStringContainsString('bar_code', $payload);
    }
}
