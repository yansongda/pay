<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Member\Ocr;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Member\Ocr\ServerDetectPlugin;
use Yansongda\Pay\Tests\TestCase;

class ServerDetectPluginTest extends TestCase
{
    protected ServerDetectPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ServerDetectPlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([
            'ocr_type' => 'IDENTITY_CARD',
            '_multipart' => [
                [
                    'name' => 'file_content',
                    'contents' => 'binary-image',
                    'filename' => 'ocr.jpg',
                ],
            ],
        ]), fn ($rocket) => $rocket);

        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('/v3/datadigital/fincloud/generalsaas/ocr/server/detect', $result->getPayload()->get('_url'));
        self::assertSame(['ocr_type' => 'IDENTITY_CARD'], $result->getPayload()->get('_body'));
        self::assertArrayHasKey('_multipart', $result->getParams());
    }
}
