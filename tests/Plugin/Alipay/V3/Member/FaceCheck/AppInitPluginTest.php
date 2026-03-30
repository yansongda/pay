<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Member\FaceCheck;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Member\FaceCheck\AppInitPlugin;
use Yansongda\Pay\Tests\TestCase;

class AppInitPluginTest extends TestCase
{
    protected AppInitPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AppInitPlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([
            'outer_order_no' => '202603250001',
        ]), fn ($rocket) => $rocket);

        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('/v3/datadigital/fincloud/generalsaas/face/check/initialize', $result->getPayload()->get('_url'));
        self::assertSame('DATA_DIGITAL_BIZ_CODE_FACE_CHECK_LIVE', $result->getPayload()->get('_body')['biz_code']);
        self::assertSame('202603250001', $result->getPayload()->get('_body')['outer_order_no']);
    }
}
