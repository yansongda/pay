<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay;

use Yansongda\Pay\Plugin\Alipay\RadarPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class RadarPluginTest extends TestCase
{
    public function testPostNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['name' => 'yansongda']));

        $plugin = new RadarPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('name=yansongda', $result->getRadar()->getBody()->getContents());
        self::assertEquals('POST', $result->getRadar()->getMethod());
    }

    public function testGetNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_method' => 'get'])->setPayload(new Collection(['name' => 'yansongda']));

        $plugin = new RadarPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('GET', $result->getRadar()->getMethod());
    }
}
