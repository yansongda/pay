<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Member\FaceVerification;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Member\FaceVerification\ServerVerifyPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;

class ServerVerifyPluginTest extends TestCase
{
    protected ServerVerifyPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ServerVerifyPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('datadigital.fincloud.generalsaas.face.source.certify', $result->getPayload()->toJson());
        self::assertStringContainsString('IDENTITY_CARD', $result->getPayload()->toJson());
    }
}
