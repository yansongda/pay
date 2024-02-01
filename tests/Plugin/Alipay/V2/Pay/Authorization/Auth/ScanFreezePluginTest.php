<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Pay\Authorization\Auth;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Auth\ScanFreezePlugin;
use Yansongda\Pay\Tests\TestCase;

class ScanFreezePluginTest extends TestCase
{
    protected ScanFreezePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ScanFreezePlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.fund.auth.order.voucher.create', $result->getPayload()->toJson());
        self::assertStringContainsString('PREAUTH_PAY', $result->getPayload()->toJson());
    }
}
