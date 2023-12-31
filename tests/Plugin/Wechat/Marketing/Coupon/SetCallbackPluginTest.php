<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Marketing\Coupon;

use Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\SetCallbackPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class SetCallbackPluginTest extends TestCase
{
    protected SetCallbackPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new SetCallbackPlugin();
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
