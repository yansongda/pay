<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Pay\Authorization;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\PosFreezePlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;

class PosFreezePluginTest extends TestCase
{
    protected PosFreezePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PosFreezePlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.fund.auth.order.freeze', $result->getPayload()->toJson());
        self::assertStringContainsString('PREAUTH_PAY', $result->getPayload()->toJson());
        self::assertStringContainsString('bar_code', $result->getPayload()->toJson());
    }
}
