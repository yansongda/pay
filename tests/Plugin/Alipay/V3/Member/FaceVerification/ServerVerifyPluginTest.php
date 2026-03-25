<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Member\FaceVerification;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Member\FaceVerification\ServerVerifyPlugin;
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
        $result = $this->plugin->assembly((new Rocket())->setParams([
            'cert_name' => 'yansongda',
            '_multipart' => [
                [
                    'name' => 'file_content',
                    'contents' => 'binary-image',
                    'filename' => 'face.jpg',
                ],
            ],
        ]), fn ($rocket) => $rocket);

        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('/v3/datadigital/fincloud/generalsaas/face/source/certify', $result->getPayload()->get('_url'));
        self::assertSame('IDENTITY_CARD', $result->getPayload()->get('_body')['cert_type']);
        self::assertSame('yansongda', $result->getPayload()->get('_body')['cert_name']);
        self::assertArrayHasKey('_multipart', $result->getParams());
    }
}
