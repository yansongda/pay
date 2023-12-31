<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Extend\ProfitSharing;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\Extend\ProfitSharing\QueryReturnPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryReturnPluginTest extends TestCase
{
    protected QueryReturnPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryReturnPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 查询分账结果, 缺少必要参数 `out_order_no`, `out_return_no`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "out_return_no" => "yansongda",
            'out_order_no' => '111',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/profitsharing/return-orders/yansongda?out_order_no=111',
            '_service_url' => 'v3/profitsharing/return-orders/yansongda?sub_mchid=null&out_order_no=111',
        ], $result->getPayload()->all());
    }

    public function testService()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            "out_return_no" => "yansongda",
            'out_order_no' => '111',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/profitsharing/return-orders/yansongda?out_order_no=111',
            '_service_url' => 'v3/profitsharing/return-orders/yansongda?sub_mchid=1600314070&out_order_no=111',
        ], $result->getPayload()->all());
    }

    public function testServiceParams()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            "out_return_no" => "yansongda",
            'out_order_no' => '111',
            'sub_mchid' => '222',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/profitsharing/return-orders/yansongda?out_order_no=111',
            '_service_url' => 'v3/profitsharing/return-orders/yansongda?sub_mchid=222&out_order_no=111',
        ], $result->getPayload()->all());
    }
}
