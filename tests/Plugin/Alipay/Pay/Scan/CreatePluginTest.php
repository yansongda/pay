<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Pay\Scan;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\Pay\Scan\CreatePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class CreatePluginTest extends TestCase
{
    protected CreatePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CreatePlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.create', $result->getPayload()->toJson());
    }
}
