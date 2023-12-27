<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Marketing\Coupon;

use Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\CreatePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CreatePluginTest extends TestCase
{
    protected CreatePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CreatePlugin();
    }

    public function testNormalParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "test" => "yansongda",
            'belong_merchant' => '1111',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/marketing/favor/coupon-stocks',
            '_service_url' => 'v3/marketing/favor/coupon-stocks',
            'test' => 'yansongda',
            'belong_merchant' => '1111',
        ], $result->getPayload()->all());
    }
    
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "test" => "yansongda",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/marketing/favor/coupon-stocks',
            '_service_url' => 'v3/marketing/favor/coupon-stocks',
            'test' => 'yansongda',
            'belong_merchant' => '1600314069',
        ], $result->getPayload()->all());
    }
}
