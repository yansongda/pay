<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Marketing\Coupon\Callback;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Callback\SetPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class SetPluginTest extends TestCase
{
    protected SetPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new SetPlugin();
    }

    public function testNormalParams()
    {
        $payload = [
            "mchid" => "yansongda",
            "notify_url" => "https://www.yansongda.cn",
            'test' => 'aaa',
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/marketing/favor/callbacks',
            '_service_url' => 'v3/marketing/favor/callbacks',
            'mchid' => 'yansongda',
            'notify_url' => 'https://www.yansongda.cn',
            'test' => 'aaa',
        ], $result->getPayload()->all());
    }

    public function testNormal()
    {
        $payload = [
            'test' => 'aaa',
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/marketing/favor/callbacks',
            '_service_url' => 'v3/marketing/favor/callbacks',
            'mchid' => '1600314069',
            'notify_url' => 'https://pay.yansongda.cn',
            'test' => 'aaa',
        ], $result->getPayload()->all());
    }
}
