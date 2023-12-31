<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Pay\Combine;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\Pay\Combine\ClosePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class ClosePluginTest extends TestCase
{
    protected ClosePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ClosePlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 合单关单，参数缺少 `combine_out_trade_no`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "combine_out_trade_no" => "111",
            'combine_appid' => '333',
            'name' => 'yansongda',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/combine-transactions/out-trade-no/111/close',
            '_service_url' => 'v3/combine-transactions/out-trade-no/111/close',
            'combine_appid' => '333',
            'name' => 'yansongda',
        ], $result->getPayload()->all());
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "combine_out_trade_no" => "111",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/combine-transactions/out-trade-no/111/close',
            '_service_url' => 'v3/combine-transactions/out-trade-no/111/close',
            'combine_appid' => 'wx55955316af4ef13',
        ], $result->getPayload()->all());
    }
}
