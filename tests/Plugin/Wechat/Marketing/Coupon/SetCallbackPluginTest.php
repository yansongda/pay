<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Marketing\Coupon;

use GuzzleHttp\Psr7\Uri;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\SetCallbackPlugin;
use Yansongda\Pay\Provider\Wechat;
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

    public function testNormal()
    {
        $rocket = (new Rocket())->setParams([])->setPayload(new Collection([
            'enable' => true,
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) {return $rocket; });

        $radar = $result->getRadar();

        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'v3/marketing/favor/callbacks'), $radar->getUri());
        self::assertEquals([
            'enable' => true,
            'mchid' => '1600314069',
        ], $result->getPayload()->all());
    }
}
