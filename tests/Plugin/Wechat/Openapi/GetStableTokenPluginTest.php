<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Openapi;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Openapi\GetStableTokenPlugin;
use Yansongda\Pay\Tests\TestCase;

class GetStableTokenPluginTest extends TestCase
{
    protected GetStableTokenPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new GetStableTokenPlugin();
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
