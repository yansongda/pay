<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Member\FaceVerification;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Member\FaceVerification\AppQueryPlugin;
use Yansongda\Pay\Tests\TestCase;

class AppQueryPluginTest extends TestCase
{
    protected AppQueryPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AppQueryPlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([
            'certify_id' => '202603250001',
        ]), fn ($rocket) => $rocket);

        self::assertSame('GET', $result->getPayload()->get('_method'));
        self::assertSame('/v3/datadigital/fincloud/generalsaas/face/verification/query?certify_id=202603250001', $result->getPayload()->get('_url'));
        self::assertSame('', $result->getPayload()->get('_body'));
    }
}
