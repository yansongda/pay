<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Pay\Mini;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\Pay\Mini\QueryPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryPluginTest extends TestCase
{
    protected QueryPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: Mini 通过商户订单号查询订单，参数缺少 `out_trade_no`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "out_trade_no" => "111",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('GET', $result->getPayload()->get('_method'));
        self::assertEquals('v3/pay/transactions/out-trade-no/111?mchid=1600314069', $result->getPayload()->get('_url'));
    }

    public function testServiceParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "out_trade_no" => "111",
            'sub_mchid' => '333',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('GET', $result->getPayload()->get('_method'));
        self::assertEquals('v3/pay/partner/transactions/out-trade-no/111?sp_mchid=1600314069&sub_mchid=333', $result->getPayload()->get('_service_url'));
    }

    public function testService()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            "out_trade_no" => "111",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('GET', $result->getPayload()->get('_method'));
        self::assertEquals('v3/pay/partner/transactions/out-trade-no/111?sp_mchid=1600314069&sub_mchid=1600314070', $result->getPayload()->get('_service_url'));
    }
}
