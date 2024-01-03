<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Member\FaceVerification;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Member\FaceVerification\H5InitPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class H5InitPluginTest extends TestCase
{
    protected H5InitPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new H5InitPlugin();
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
