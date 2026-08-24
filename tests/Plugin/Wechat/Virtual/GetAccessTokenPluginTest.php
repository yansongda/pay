<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\GetAccessTokenPlugin;
use Yansongda\Pay\Tests\TestCase;

class GetAccessTokenPluginTest extends TestCase
{
    protected GetAccessTokenPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new GetAccessTokenPlugin();
    }

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('/cgi-bin/stable_token', $payload->get('_url'));
    }
}