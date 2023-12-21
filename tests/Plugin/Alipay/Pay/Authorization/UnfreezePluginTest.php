<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Pay\Authorization;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\Pay\Authorization\UnfreezePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class UnfreezePluginTest extends TestCase
{
    protected UnfreezePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new UnfreezePlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.fund.auth.order.unfreeze', $result->getPayload()->toJson());
    }
}
