<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Pay\Authorization;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\Pay\Authorization\AppFreezePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class AppFreezePluginTest extends TestCase
{
    protected AppFreezePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AppFreezePlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.fund.auth.order.app.freeze', $result->getPayload()->toJson());
        self::assertStringContainsString('PREAUTH_PAY', $result->getPayload()->toJson());
    }
}
