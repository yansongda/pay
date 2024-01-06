<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V2;

use Yansongda\Pay\Packer\XmlPacker;
use Yansongda\Pay\Plugin\Wechat\V2\AddPayloadBodyPlugin;
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
        $rocket->setPacker(XmlPacker::class)->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame((new XmlPacker())->pack($payload), $result->getPayload()->get('_body'));
    }

    public function testUnderline()
    {
        $payload = [
            "name" => "yansongda",
            '_age' => 30,
            'aaa' => null,
        ];

        $rocket = new Rocket();
        $rocket->setPacker(XmlPacker::class)->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        unset($payload['_age'], $payload['aaa']);

        self::assertSame((new XmlPacker())->pack($payload), $result->getPayload()->get('_body'));
    }

    public function testEmpty()
    {
        $payload = [
            '_age' => '30',
        ];

        $rocket = new Rocket();
        $rocket->setPacker(XmlPacker::class)->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame('<xml></xml>', $result->getPayload()->get('_body'));
    }
}
