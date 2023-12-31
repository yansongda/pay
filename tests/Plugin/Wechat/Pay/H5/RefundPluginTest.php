<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Pay\H5;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\Pay\H5\RefundPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class RefundPluginTest extends TestCase
{
    protected RefundPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RefundPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: H5 退款申请，参数为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "notify_url" => "111",
            'name' => '222',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/refund/domestic/refunds',
            '_service_url' => 'v3/refund/domestic/refunds',
            'notify_url' => '111',
            'name' => '222',
        ], $result->getPayload()->all());
    }
    
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "out_trade_no" => "111",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/refund/domestic/refunds',
            '_service_url' => 'v3/refund/domestic/refunds',
            'notify_url' => 'https://pay.yansongda.cn',
            'out_trade_no' => '111',
        ], $result->getPayload()->all());
    }

    public function testServiceParams()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            "notify_url" => "111",
            'sub_mchid' => '222',
            'name' => 'yansongda',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/refund/domestic/refunds',
            '_service_url' => 'v3/refund/domestic/refunds',
            'notify_url' => '111',
            'sub_mchid' => '222',
            'name' => 'yansongda',
        ], $result->getPayload()->all());
    }

    public function testService()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
            'name' => 'yansongda',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/refund/domestic/refunds',
            '_service_url' => 'v3/refund/domestic/refunds',
            'name' => 'yansongda',
            'notify_url' => null,
            'sub_mchid' => '1600314070',
        ], $result->getPayload()->all());
    }
}
