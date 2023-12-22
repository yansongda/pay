<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Member\FaceVerification;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\Member\FaceVerification\WapInitPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class WapInitPluginTest extends TestCase
{
    protected WapInitPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new WapInitPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('datadigital.fincloud.generalsaas.face.certify.initialize', $result->getPayload()->toJson());
        self::assertStringContainsString('FUTURE_TECH_BIZ_FACE_SDK', $result->getPayload()->toJson());
    }
}
