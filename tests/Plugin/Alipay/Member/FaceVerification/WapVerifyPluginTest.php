<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Member\FaceVerification;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\Member\FaceVerification\WapVerifyPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class WapVerifyPluginTest extends TestCase
{
    protected WapVerifyPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new WapVerifyPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('datadigital.fincloud.generalsaas.face.certify.verify', $result->getPayload()->toJson());
    }
}
