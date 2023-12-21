<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Member\Authorization;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\Member\Authorization\TokenPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class TokenPluginTest extends TestCase
{
    protected TokenPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new TokenPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload()->toJson();

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.system.oauth.token', $payload);
    }
}
