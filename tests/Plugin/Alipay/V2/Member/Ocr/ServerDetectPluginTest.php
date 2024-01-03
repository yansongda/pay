<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Member\Ocr;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Member\Ocr\ServerDetectPlugin;
use Yansongda\Pay\Rocket;
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
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('datadigital.fincloud.generalsaas.ocr.server.detect', $result->getPayload()->toJson());
    }
}
