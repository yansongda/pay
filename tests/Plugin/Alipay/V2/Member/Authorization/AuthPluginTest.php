<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Member\Authorization;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\V2\Member\Authorization\AuthPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class AuthPluginTest extends TestCase
{
    protected AuthPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AuthPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload()->toJson();

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.user.info.auth', $payload);
    }
}
