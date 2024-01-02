<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Pay\Mini;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\PayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class PayPluginTest extends TestCase
{
    protected PayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PayPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: Mini 下单，参数为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "name" => "yansongda",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/pay/transactions/jsapi',
            '_service_url' => 'v3/pay/partner/transactions/jsapi',
            "appid" => "wx55955316af4ef14",
            'mchid' => '1600314069',
            'notify_url' => 'https://pay.yansongda.cn',
            'name' => 'yansongda',
        ], $result->getPayload()->all());
    }

    public function testServiceParams()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            'sub_mchid' => '333',
            'notify_url' => '444',
            'name' => 'yansongda',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/pay/transactions/jsapi',
            '_service_url' => 'v3/pay/partner/transactions/jsapi',
            "sp_appid" => "wx55955316af4ef14",
            'sp_mchid' => '1600314069',
            'sub_mchid' => '333',
            'notify_url' => '444',
            'name' => 'yansongda',
        ], $result->getPayload()->all());
    }

    public function testService()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            'name' => 'yansongda',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/pay/transactions/jsapi',
            '_service_url' => 'v3/pay/partner/transactions/jsapi',
            "sp_appid" => "wx55955316af4ef14",
            'sp_mchid' => '1600314069',
            'sub_mchid' => '1600314070',
            'notify_url' => '',
            'name' => 'yansongda',
        ], $result->getPayload()->all());
    }
}
