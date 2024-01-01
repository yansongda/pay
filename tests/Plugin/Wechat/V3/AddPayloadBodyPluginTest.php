<?php

namespace Plugin\Wechat\V3;

use Yansongda\Pay\Packer\JsonPacker;
use Yansongda\Pay\Plugin\Wechat\V3\AddPayloadBodyPlugin;
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

        self::assertSame((new JsonPacker())->pack($payload), $result->getPayload()->get('_body'));
    }

    public function testUnderline()
    {
        $payload = [
            "name" => "yansongda",
            '_age' => 30,
            'aaa' => null,
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        unset($payload['_age'], $payload['aaa']);

        self::assertSame((new JsonPacker())->pack($payload), $result->getPayload()->get('_body'));
    }

    public function testEmpty()
    {
        $payload = [
            '_age' => '30',
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame('', $result->getPayload()->get('_body'));
    }
}
