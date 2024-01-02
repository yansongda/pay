<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Member\Ocr;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Member\Ocr\DetectPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class DetectPluginTest extends TestCase
{
    protected DetectPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new DetectPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('datadigital.fincloud.generalsaas.ocr.common.detect', $result->getPayload()->toJson());
    }
}
