<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Member\FaceVerification;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Member\FaceVerification\H5VerifyPlugin;
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
        $result = $this->plugin->assembly((new Rocket())->setParams([
            'certify_id' => '202603250001',
        ]), fn ($rocket) => $rocket);

        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('/v3/datadigital/fincloud/generalsaas/face/certify/verify', $result->getPayload()->get('_url'));
        self::assertSame(['certify_id' => '202603250001'], $result->getPayload()->get('_body'));
    }
}
