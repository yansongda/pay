<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Pay\Web;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\Pay\Web\ClosePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class ClosePluginTest extends TestCase
{
    protected ClosePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ClosePlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.close', $result->getPayload()->toJson());
    }
}
