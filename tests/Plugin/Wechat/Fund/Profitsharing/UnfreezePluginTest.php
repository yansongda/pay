<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Fund\Profitsharing;

use GuzzleHttp\Psr7\Uri;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing\UnfreezePlugin;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class UnfreezePluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection());

        $plugin = new UnfreezePlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();
        $payload = $result->getPayload();

        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'v3/profitsharing/orders/unfreeze'), $radar->getUri());
        self::assertEquals('POST', $radar->getMethod());
        self::assertArrayNotHasKey('sub_mchid', $payload->all());
    }

    public function testPartner()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection());

        $plugin = new UnfreezePlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();
        $payload = $result->getPayload();

        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_SERVICE].'v3/profitsharing/orders/unfreeze'), $radar->getUri());
        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals('1600314070', $payload->get('sub_mchid'));
    }

    public function testPartnerDirectPayload()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection(['sub_mchid' => '123']));

        $plugin = new UnfreezePlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('123', $payload->get('sub_mchid'));
    }
}
