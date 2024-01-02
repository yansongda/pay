<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Extend\ProfitSharing;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\ProfitSharing\QueryPlugin;
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
        self::expectExceptionMessage('参数异常: 查询分账结果, 缺少必要参数 `out_order_no`, `transaction_id`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "transaction_id" => "yansongda",
            'out_order_no' => '111',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/profitsharing/orders/111?transaction_id=yansongda',
            '_service_url' => 'v3/profitsharing/orders/111?sub_mchid=null&transaction_id=yansongda',
        ], $result->getPayload()->all());
    }

    public function testService()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            "transaction_id" => "yansongda",
            'out_order_no' => '111',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/profitsharing/orders/111?transaction_id=yansongda',
            '_service_url' => 'v3/profitsharing/orders/111?sub_mchid=1600314070&transaction_id=yansongda',
        ], $result->getPayload()->all());
    }

    public function testServiceParams()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            "transaction_id" => "yansongda",
            'out_order_no' => '111',
            'sub_mchid' => '222',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/profitsharing/orders/111?transaction_id=yansongda',
            '_service_url' => 'v3/profitsharing/orders/111?sub_mchid=222&transaction_id=yansongda',
        ], $result->getPayload()->all());
    }
}
