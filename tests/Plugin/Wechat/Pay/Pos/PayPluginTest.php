<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Pay\Pos;

use GuzzleHttp\Psr7\Uri;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Pay\Pos\PayPlugin;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class PayPluginTest extends \Yansongda\Pay\Tests\TestCase
{
    protected PayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PayPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['out_trade_no' => '123']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();
        $payload = $result->getPayload();
        $params = $result->getParams();

        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'pay/micropay'), $radar->getUri());
        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals('1600314069', $payload->get('mchid'));
        self::assertEquals('wx55955316af4ef13', $payload->get('app_id'));
        self::assertEquals('v2', $params['_version']);
        self::assertEquals([
            'Content-Type' => 'application/xml',
            'User-Agent' => 'yansongda/pay-v3',
        ], $radar->getHeaders());
    }

    public function testMultiType()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_type' => 'app'])->setPayload(new Collection());

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();
        $payload = $result->getPayload();
        $params = $result->getParams();

        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'pay/micropay'), $radar->getUri());
        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals('1600314069', $payload->get('mchid'));
        self::assertEquals('yansongda', $payload->get('app_id'));
        self::assertEquals('v2', $params['_version']);
        self::assertEquals([
            'Content-Type' => 'application/xml',
            'User-Agent' => 'yansongda/pay-v3',
        ], $radar->getHeaders());
    }
}