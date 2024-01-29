<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Pay\Agreement\Sign;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Sign\UnsignPlugin;
use Yansongda\Pay\Tests\TestCase;

class UnsignPluginTest extends TestCase
{
    protected UnsignPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new UnsignPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.user.agreement.unsign', $result->getPayload()->toJson());
    }
}
