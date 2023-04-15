<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\User;

use Yansongda\Pay\Contract\DirectionInterface;
use Yansongda\Pay\Plugin\Alipay\User\InfoSharePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class InfoSharePluginTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new InfoSharePlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals(DirectionInterface::class, $result->getDirection());
        self::assertStringContainsString('alipay.user.info.share', $result->getPayload()->toJson());
        self::assertStringContainsString('auth_token', $result->getPayload()->toJson());
    }
}
