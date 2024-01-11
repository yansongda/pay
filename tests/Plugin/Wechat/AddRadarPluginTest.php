<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat;

use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
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

    public function testNormal()
    {
        $params = [];
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => 'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi',
            '_body' => '123',
        ]);

        $rocket = (new Rocket())->setParams($params)->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertFalse($radar->hasHeader('Authorization'));
        self::assertFalse($radar->hasHeader('Wechatpay-Serial'));
        self::assertEquals('application/json, text/plain, application/x-gzip', $radar->getHeaderLine('Accept'));
        self::assertEquals('yansongda/pay-v3', $radar->getHeaderLine('User-Agent'));
        self::assertEquals('application/json; charset=utf-8', $radar->getHeaderLine('Content-Type'));
        self::assertEquals('123', (string) $radar->getBody());
        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals('https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi', (string) $radar->getUri());
    }

    public function testNormalWithAuthorization()
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

        self::assertEquals('yansongda', $radar->getHeaderLine('Wechatpay-Serial'));
    }

    public function testNormalWithContentType()
    {
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => 'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi',
            '_body' => '123',
            '_content_type' => 'yansongda',
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('yansongda', $radar->getHeaderLine('Content-Type'));
    }

    public function testNormalWithAccept()
    {
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => 'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi',
            '_body' => '123',
            '_accept' => 'yansongda',
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('yansongda', $radar->getHeaderLine('Accept'));
    }
}
