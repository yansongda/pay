<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Plugin\Alipay\LaunchPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class LaunchPluginTest extends TestCase
{
    private $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new LaunchPlugin();
    }

    public function testNoHttpRequest()
    {
        $rocket = new Rocket();
        $rocket->setDirection(NoHttpRequestParser::class);

        self::assertSame($rocket, $this->plugin->assembly($rocket, function ($rocket) { return $rocket; }));
    }

    public function testNormal()
    {
        $response = [
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
            "alipay_cert_sn" => "a359aaadd01ceca03dbc07537da539b9",
            "sign" => "Ipp1M3pwUFJ19Tx/D+40RZstXr3VSZzGxPB1Qfj1e837UkGxOJxFFK6EZ288SeEh06dPFd4qJ7BHfP/7mvkRqF1/mezBGvhBz03XTXfDn/O6IkoA+cVwpfm+i8MFvzC/ZQB0dgtZppu5qfzVyFaaNu8ct3L/NSQCMR1RXg2lH3HiwfxmIF35+LmCoL7ZPvTxB/epm7A/XNhAjLpK5GlJffPA0qwhhtQwaIZ7DHMXo06z03fbgxlBu2eEclQUm6Fobgj3JEERWLA0MDQiV1EYNWuHSSlHCMrIxWHba+Euu0jVkKKe0IFKsU8xJQbc7GTJXx/o0NfHqGwwq8hMvtgBkg==",
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['method' => 'alipay.trade.query']))
            ->setDestination(new Collection($response))
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEqualsCanonicalizing($response['alipay_trade_query_response'], $result->getDestination()->all());
    }

    public function testCodeError()
    {
        $response = [
            'alipay_trade_query_response' => [
                "code" => "40004",
                "msg" => "Business Failed",
                "sub_code" => "ACQ.TRADE_NOT_EXIST",
                "sub_msg" => "交易不存在",
                "buyer_pay_amount" => "0.00",
                "invoice_amount" => "0.00",
                "out_trade_no" => "1622819957",
                "point_amount" => "0.00",
                "receipt_amount" => "0.00",
            ],
            'alipay_cert_sn' => 'a359aaadd01ceca03dbc07537da539b9',
            'sign' => 'OaQiIXuxZeMWccI/gV0/f0YFKmR0zUsUSA+pOUghMJjsbL7W+mNw4Wvk8NFJzlk0EcwV+BpvT/NFl5oSPN2NTn4JbHheVkN9DvYDK8UacvUjnDLO4vZ2Z828he8CF77ktieTjrzxo5b6dguMnOFeew+YAzSCZaiV2sSUSc6K42yiSC290B80jBUbNKE10sUDWR8OKPYqHxMlbtPyGv2jSxNoDIIP7VIGKNzU8i7dbNOYCrAviBXcDrR/m9ncYfIJfhn1yHPtLCGUUcJKToPsvE0+4Q3gS4n+wMHhCcbq02qnwhPSRbmsPS0E7D5JNqVmiXIc2XeEffKYFy1kQKvGGQ==',
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['method' => 'alipay.trade.query']))
            ->setDestination(new Collection($response))
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('40004', $result->getDestination()->get('code'));
    }

    public function testWrongSign()
    {
        $response = [
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
            "alipay_cert_sn" => "a359aaadd01ceca03dbc07537da539b9",
            "sign" => "pp1M3pwUFJ19Tx/D+40RZstXr3VSZzGxPB1Qfj1e837UkGxOJxFFK6EZ288SeEh06dPFd4qJ7BHfP/7mvkRqF1/mezBGvhBz03XTXfDn/O6IkoA+cVwpfm+i8MFvzC/ZQB0dgtZppu5qfzVyFaaNu8ct3L/NSQCMR1RXg2lH3HiwfxmIF35+LmCoL7ZPvTxB/epm7A/XNhAjLpK5GlJffPA0qwhhtQwaIZ7DHMXo06z03fbgxlBu2eEclQUm6Fobgj3JEERWLA0MDQiV1EYNWuHSSlHCMrIxWHba+Euu0jVkKKe0IFKsU8xJQbc7GTJXx/o0NfHqGwwq8hMvtgBkg==",
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['method' => 'alipay.trade.query']))
            ->setDestination(new Collection($response))
            ->setParams([]);

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(InvalidResponseException::INVALID_RESPONSE_SIGN);
        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testErrorResponseWithNoMethodKey()
    {
        $response = [
            'error_response' => [
                "code" => "40004",
                "msg" => "Invalid Arguments",
                "sub_code" => "isv.code-invalid",
                "sub_msg" => "授权码code无效",
            ],
            'alipay_cert_sn' => 'a359aaadd01ceca03dbc07537da539b9',
            'sign' => 'OaQiIXuxZeMWccI/gV0/f0YFKmR0zUsUSA+pOUghMJjsbL7W+mNw4Wvk8NFJzlk0EcwV+BpvT/NFl5oSPN2NTn4JbHheVkN9DvYDK8UacvUjnDLO4vZ2Z828he8CF77ktieTjrzxo5b6dguMnOFeew+YAzSCZaiV2sSUSc6K42yiSC290B80jBUbNKE10sUDWR8OKPYqHxMlbtPyGv2jSxNoDIIP7VIGKNzU8i7dbNOYCrAviBXcDrR/m9ncYfIJfhn1yHPtLCGUUcJKToPsvE0+4Q3gS4n+wMHhCcbq02qnwhPSRbmsPS0E7D5JNqVmiXIc2XeEffKYFy1kQKvGGQ==',
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['method' => 'alipay.trade.query']))
            ->setDestination(new Collection($response))
            ->setParams([]);

        try {
            $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        } catch (InvalidResponseException $e) {
            self::assertEquals($response, $e->response->all());
        }
    }
}
