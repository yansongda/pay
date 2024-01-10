<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Pay\Face;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Face\InitPlugin;
use Yansongda\Artful\Rocket;
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
