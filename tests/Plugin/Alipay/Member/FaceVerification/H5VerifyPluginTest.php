<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Member\FaceVerification;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\Member\FaceVerification\H5VerifyPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class H5VerifyPluginTest extends TestCase
{
    protected H5VerifyPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new H5VerifyPlugin();
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
