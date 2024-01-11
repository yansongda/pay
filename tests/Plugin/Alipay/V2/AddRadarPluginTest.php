<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2;

use Yansongda\Pay\Plugin\Alipay\V2\AddRadarPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AddRadarPluginTest extends TestCase
{
    protected AddRadarPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AddRadarPlugin();
    }

    public function testRadarPostNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['name' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('https://openapi.alipay.com/gateway.do?charset=utf-8', (string) $result->getRadar()->getUri());
        self::stringContains('name=yansongda', (string) $result->getRadar()->getBody());
        self::assertEquals('POST', $result->getRadar()->getMethod());
    }

    public function testRadarGetNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_method' => 'get'])->setPayload(new Collection(['name' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('https://openapi.alipay.com/gateway.do?charset=utf-8', (string) $result->getRadar()->getUri());
        self::assertEquals('GET', $result->getRadar()->getMethod());
    }
}
