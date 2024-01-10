<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Member\FaceVerification;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Member\FaceVerification\H5QueryPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;

class H5QueryPluginTest extends TestCase
{
    protected H5QueryPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new H5QueryPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('datadigital.fincloud.generalsaas.face.certify.query', $result->getPayload()->toJson());
    }
}
