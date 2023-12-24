<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat;

use Yansongda\Pay\Plugin\Wechat\AddPayloadBodyPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AddPayloadBodyPluginTest extends TestCase
{
    protected AddPayloadBodyPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AddPayloadBodyPlugin();
    }

    public function testNormal()
    {
        $payload = [
            "name" => "yansongda",
            'age' => 30,
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame(json_encode($payload), $result->getPayload()->get('_body'));
    }

    public function testEmpty()
    {
        $payload = [];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame('', $result->getPayload()->get('_body'));
    }
}
