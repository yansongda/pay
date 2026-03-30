<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Member\FaceVerification;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Member\FaceVerification\H5InitPlugin;
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
        $result = $this->plugin->assembly((new Rocket())->setParams([
            'outer_order_no' => '202603250001',
        ]), fn ($rocket) => $rocket);

        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('/v3/datadigital/fincloud/generalsaas/face/certify/initialize', $result->getPayload()->get('_url'));
        self::assertSame('FUTURE_TECH_BIZ_FACE_SDK', $result->getPayload()->get('_body')['biz_code']);
        self::assertSame('202603250001', $result->getPayload()->get('_body')['outer_order_no']);
    }
}
