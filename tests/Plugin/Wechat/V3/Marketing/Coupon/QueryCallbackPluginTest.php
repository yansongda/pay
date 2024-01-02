<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Marketing\Coupon;

use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\QueryCallbackPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryCallbackPluginTest extends TestCase
{
    protected QueryCallbackPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryCallbackPlugin();
    }

    public function testNormalParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "mchid" => "yansongda",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/marketing/favor/callbacks?mchid=yansongda',
            '_service_url' => 'v3/marketing/favor/callbacks?mchid=yansongda',
        ], $result->getPayload()->all());
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/marketing/favor/callbacks?mchid=1600314069',
            '_service_url' => 'v3/marketing/favor/callbacks?mchid=1600314069',
        ], $result->getPayload()->all());
    }
}
