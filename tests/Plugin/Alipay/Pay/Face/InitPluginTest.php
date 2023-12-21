<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Pay\Face;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\Pay\Face\InitPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class InitPluginTest extends TestCase
{
    protected InitPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new InitPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload()->toJson();

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('zoloz.authentication.smilepay.initialize', $payload);
    }
}
