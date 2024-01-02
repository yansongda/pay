<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Pay\Authorization;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\DetailPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class DetailPluginTest extends TestCase
{
    protected DetailPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new DetailPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.fund.auth.operation.detail.query', $result->getPayload()->toJson());
    }
}
