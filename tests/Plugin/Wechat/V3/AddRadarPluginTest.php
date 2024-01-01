<?php

namespace Plugin\Wechat\V3;

use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Rocket;
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

    public function testNormal()
    {
        $params = [];
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => 'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi',
            '_body' => '123',
            '_authorization' => '456',
        ]);

        $rocket = (new Rocket())->setParams($params)->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertTrue($radar->hasHeader('Authorization'));
        self::assertFalse($radar->hasHeader('Wechatpay-Serial'));
        self::assertEquals('123', (string) $radar->getBody());
        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals('https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi', (string) $radar->getUri());
    }

    public function testNormalWithWechatSerial()
    {
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => 'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi',
            '_body' => '123',
            '_authorization' => '456',
            '_serial_no' => 'yansongda',
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertTrue($radar->hasHeader('Wechatpay-Serial'));
    }
}
