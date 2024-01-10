<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Pay\Scan;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\CreatePlugin;
use Yansongda\Artful\Rocket;
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
