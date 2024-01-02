<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Marketing\Redpack;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Marketing\Redpack\WebPayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class WebPayPluginTest extends TestCase
{
    protected WebPayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new WebPayPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.fund.trans.page.pay', $result->getPayload()->toJson());
        self::assertStringContainsString('STD_APP_TRANSFER', $result->getPayload()->toJson());
    }
}
