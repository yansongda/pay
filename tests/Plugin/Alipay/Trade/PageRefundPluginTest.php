<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Trade;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\Trade\PageRefundPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class PageRefundPluginTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PageRefundPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.page.refund', $result->getPayload()->toJson());
    }
}
