<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2;

use Yansongda\Pay\Plugin\Alipay\V2\ResponsePlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class ResponsePluginTest extends TestCase
{
    private ResponsePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ResponsePlugin();
    }

    public function testNormal()
    {
        $destination = [
            "alipay_trade_query_response" => [
                "code" => "10000",
                "msg" => "Success",
                "buyer_logon_id" => "ghd***@sandbox.com",
                "buyer_pay_amount" => "0.00",
                "buyer_user_id" => "2088102174698127",
                "buyer_user_type" => "PRIVATE",
                "invoice_amount" => "0.00",
                "out_trade_no" => "yansongda-1622986519",
                "point_amount" => "0.00",
                "receipt_amount" => "0.00",
                "send_pay_date" => "2021-06-06 21:35:40",
                "total_amount" => "0.01",
                "trade_no" => "2021060622001498120501382075",
                "trade_status" => "TRADE_SUCCESS",
              ],
            "sign" => "123",
        ];

        $rocket = (new Rocket())
            ->mergePayload(['method' => 'alipay.trade.query'])
            ->setDestination(new Collection($destination));

        $result = $this->plugin->assembly($rocket, function ($rocket) {return $rocket;});

        self::assertEquals(
            array_merge(['_sign' => '123'], $destination['alipay_trade_query_response']),
            $result->getDestination()->all()
        );
    }

    public function testErrorResponseWithNoMethodKey()
    {
        $destination = [
            "alipay_trade_query_response" => [
                "code" => "10000",
                "msg" => "Success",
                "buyer_logon_id" => "ghd***@sandbox.com",
                "buyer_pay_amount" => "0.00",
                "buyer_user_id" => "2088102174698127",
                "buyer_user_type" => "PRIVATE",
                "invoice_amount" => "0.00",
                "out_trade_no" => "yansongda-1622986519",
                "point_amount" => "0.00",
                "receipt_amount" => "0.00",
                "send_pay_date" => "2021-06-06 21:35:40",
                "total_amount" => "0.01",
                "trade_no" => "2021060622001498120501382075",
                "trade_status" => "TRADE_SUCCESS",
              ],
            "sign" => "123",
        ];

        $rocket = (new Rocket())
            ->mergePayload(['method' => 'not.exist'])
            ->setDestination(new Collection($destination));

        $result = $this->plugin->assembly($rocket, function ($rocket) {return $rocket;});

        self::assertEquals(array_merge(['_sign' => '123'], $destination), $result->getDestination()->all());
    }
}
