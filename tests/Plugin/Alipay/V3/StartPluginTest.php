<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\StartPlugin;
use Yansongda\Pay\Tests\TestCase;

class StartPluginTest extends TestCase
{
    protected StartPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new StartPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('https://pay.yansongda.cn', $result->getPayload()->get('notify_url'));
        self::assertNull($result->getPayload()->get('app_auth_token'));
    }

    public function testNotifyUrlFromParams()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3', '_notify_url' => 'https://custom.notify.url']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('https://custom.notify.url', $result->getPayload()->get('notify_url'));
    }

    public function testDefaultConfig()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('https://pay.yansongda.cn', $result->getPayload()->get('notify_url'));
    }

    public function testAppAuthToken()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3', '_app_auth_token' => 'test_token']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('test_token', $result->getPayload()->get('app_auth_token'));
    }
}
