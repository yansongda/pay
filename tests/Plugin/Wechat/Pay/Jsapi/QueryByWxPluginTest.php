<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Pay\Jsapi;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\Pay\Jsapi\QueryByWxPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryByWxPluginTest extends TestCase
{
    protected QueryByWxPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryByWxPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: Jsapi 通过微信订单号查询订单，参数缺少 `transaction_id`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "transaction_id" => "111",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('GET', $result->getPayload()->get('_method'));
        self::assertEquals('v3/pay/transactions/id/111?mchid=1600314069', $result->getPayload()->get('_url'));
    }

    public function testServiceParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "transaction_id" => "111",
            'sub_mchid' => '333',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('GET', $result->getPayload()->get('_method'));
        self::assertEquals('v3/pay/partner/transactions/id/111?sp_mchid=1600314069&sub_mchid=333', $result->getPayload()->get('_service_url'));
    }

    public function testService()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            "transaction_id" => "111",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('GET', $result->getPayload()->get('_method'));
        self::assertEquals('v3/pay/partner/transactions/id/111?sp_mchid=1600314069&sub_mchid=1600314070', $result->getPayload()->get('_service_url'));
    }
}
