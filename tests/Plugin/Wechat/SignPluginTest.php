<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat;

use GuzzleHttp\Psr7\Request;
use Yansongda\Pay\Plugin\Wechat\SignPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class SignPluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\Wechat\SignPlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new SignPlugin();
    }

    public function testNormal()
    {
        $params = [
            'name' => 'yansongda',
            'age' => 28,
        ];
        $rocket = (new Rocket())->setParams($params)
                                ->setPayload(new Collection($params))
                                ->setRadar(new Request('GET', '127.0.0.1'));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertTrue($radar->hasHeader('Authorization'));
        self::assertFalse($radar->hasHeader('Wechatpay-Serial'));
        self::assertEquals(json_encode($params), $radar->getBody()->getContents());
    }

    public function testNormalWithWechatSerial()
    {
        $params = [
            '_serial_no' => 'yansongda',
            'name' => 'yansongda',
            'age' => 28,
        ];
        $rocket = (new Rocket())->setParams($params)
            ->setPayload(new Collection($params))
            ->setRadar(new Request('GET', '127.0.0.1'));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertTrue($radar->hasHeader('Authorization'));
        self::assertTrue($radar->hasHeader('Wechatpay-Serial'));
    }
}
