<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Pay\Agreement;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\ModifyPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class ModifyPluginTest extends TestCase
{
    protected ModifyPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ModifyPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.user.agreement.executionplan.modify', $result->getPayload()->toJson());
    }
}
