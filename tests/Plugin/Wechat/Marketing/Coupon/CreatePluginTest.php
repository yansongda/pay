<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Marketing\Coupon;

use GuzzleHttp\Psr7\Uri;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\CreatePlugin;
use Yansongda\Pay\Provider\Wechat;
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

    public function testNormal()
    {
        $rocket = (new Rocket())->setParams([])->setPayload(new Collection([
            'stock_name' => '7890',
            'available_begin_time' => '2020-01-01T00:00:00+08:00',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) {return $rocket; });

        $radar = $result->getRadar();

        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'v3/marketing/favor/coupon-stocks'), $radar->getUri());
        self::assertEquals([
            'stock_name' => '7890',
            'available_begin_time' => '2020-01-01T00:00:00+08:00',
            'belong_merchant' => '1600314069',
        ], $result->getPayload()->all());
    }
}
