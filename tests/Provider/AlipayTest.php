<?php

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Mockery;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\LaunchPlugin;
use Yansongda\Pay\Plugin\Alipay\PreparePlugin;
use Yansongda\Pay\Plugin\Alipay\RadarPlugin;
use Yansongda\Pay\Plugin\Alipay\RadarSignPlugin;
use Yansongda\Pay\Plugin\Alipay\SignPlugin;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Pay\Tests\TestCase;

class AlipayTest extends TestCase
{
    public function testShortcutNotFound()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(InvalidParamsException::SHORTCUT_NOT_FOUND);

        Pay::alipay()->foo();
    }

    public function testShortcutIncompatible()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(InvalidParamsException::SHORTCUT_NOT_FOUND);

        Pay::alipay()->foo();
    }

    public function testFindDefault()
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

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($response)));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::alipay()->find('yansongda-1622986519');

        self::assertEqualsCanonicalizing($response['alipay_trade_query_response'], $result->all());
    }

    public function testFindTransfer()
    {
        $response = [
            "alipay_fund_trans_common_query_response" => [
                "code" => "10000",
                "msg" => "Success",
                "order_id" => "20220903110070000006210024965252",
                "out_biz_no" => "202209032319",
                "pay_date" => "2022-09-03 23:26:58",
                'pay_fund_order_id' => '20220903110070001506210025020488',
                "status" => "SUCCESS",
            ],
            "alipay_cert_sn" => "a359aaadd01ceca03dbc07537da539b9",
            "sign" => "BD0RLfVXU/xx4c9oGcMIC+cb6kMqGVa4zCgjuFP1XnzWUqTG9eSo74YtAjI2BUFQXQEnW9ZJQZLJ0MpddsW7UJSAGWj7Yn7hGUUx3/P0b7bgqRgR2nJNak381wOcwgcyj7Iwx/wnfeFZh55dy824e1VvuSEz5izLvRfsGAwQVUACJFdoCC/J2A/uw9QaYSQ3n9ibJnQ9sPV6lXA+4oRKse7pEsbrI4YXnB7xJE8bTCW3E/YbsBDuRKE4Ja4ZcXD81a1tDMCRMSudSz8k/oazCHa0TZC4bzqm4oZUQZvu2VetnzFFUaydMX4tIIu/Xgs527j5qWjV8reM8BL19plBSg==",
        ];

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($response)));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::alipay()->find(['out_biz_no' => '202209032319', '_type' => 'transfer']);
        self::assertEqualsCanonicalizing($response['alipay_fund_trans_common_query_response'], $result->all());
    }

    public function testFindRefund()
    {
        $response = [
            "alipay_trade_fastpay_refund_query_response" => [
                "code" => "10000",
                "msg" => "Success",
                "out_request_no" => "1623160012",
                "out_trade_no" => "1623160012",
                "refund_amount" => "0.01",
                "total_amount" => "0.01",
                "trade_no" => "2021060822001498120501382932",
            ],
            "alipay_cert_sn" => "a359aaadd01ceca03dbc07537da539b9",
            "sign" => "WuqwIP2SBc7qPqwXkqI/MV2kRAbsODogr4bcHmM8svEHeNA37wGng/ApoVD1YqzTgD92dlJZ3/aUjRaLb94KyEaOGOcPLHucjRY/GyYJsmLfLfJfNjpjsglgc9ChUspkNm9r8PH+GKLm8PbY3BFyjjG59cfHIPpVHbTLxrLAcjOilKBJu1nNHiswTVGbAeHJkVrMdxRXxoBuSnuwr6fjy2h1w/evPE4dZ2xiPsKNv8B5swLd0jo0g/c6OFfF8sgFa6VbXMKyuj8/GHxwz4jxlTWdJYYWBeYlb7mXPYWnIvmM1e4qwwo4X4A9g8nDj+dsKSFyRvx879kNdtbP8B+SUw==",
        ];

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->twice()->andReturn(
            new Response(200, [], json_encode($response)), new Response(200, [], json_encode($response))
        );
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::alipay()->find([
            'out_trade_no' => '1623160012',
            'out_request_no' => '1623160012',
            '_type' => 'refund'
        ]);
        self::assertEqualsCanonicalizing($response['alipay_trade_fastpay_refund_query_response'], $result->all());

        $result1 = Pay::alipay()->find([
            'out_trade_no' => '1623160012',
            'out_request_no' => '1623160012',
        ]);
        self::assertEqualsCanonicalizing($response['alipay_trade_fastpay_refund_query_response'], $result1->all());
    }

    public function testRefund()
    {
        $response = [
            "alipay_trade_refund_response" => [
                "code" => "10000",
                "msg" => "Success",
                "buyer_logon_id" => "ghd***@sandbox.com",
                "buyer_user_id" => "2088102174698127",
                "fund_change" => "Y",
                "gmt_refund_pay" => "2021-06-08 21:48:39",
                "out_trade_no" => "1623160012",
                "refund_fee" => "0.01",
                "send_back_fee" => "0.00",
                "trade_no" => "2021060822001498120501382932",
            ],
            "alipay_cert_sn" => "a359aaadd01ceca03dbc07537da539b9",
            "sign" => "QfN3w7SAOR1FxFko05q2RXzv3hHBxVn9hT7rKpn0DrZss370iRDQQaSxy5ILjGSqSx8ODMOnUWTslzm3yk0hKEkOCTeDO5QJpDWwjBV0m7AJzFGhvh64ITrqsNk5/wID2dhlRehjF9jvJBUPMmlXEjc06B2azHrRHW8eF5z1aZLvoNXvtXQ2HzGpp5moIZMJGEsUqT+Qa172S3z6sGcPnN3rivxedcZF8OWALr0/gAvA4l7E2ZZg8c2cTsc+napTp3cuH0J8borxT5D7hDOu7xdaFA8b4YFqxQPrKFotC1vTpzxb88ImpYnCZw4vA6GLPJwYUHqHRT6C4I2bl1QTlA==",
        ];

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($response)));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::alipay()->refund(['out_trade_no' => '1623160012', 'refund_amount' => '0.01',]);

        self::assertEqualsCanonicalizing($response['alipay_trade_refund_response'], $result->all());
    }

    public function testClose()
    {
        $response = [
            "alipay_trade_close_response" => [
                "code" => "10000",
                "msg" => "Success",
                "out_trade_no" => "1623161325",
                "trade_no" => "2021060822001498120501382798",
            ],
            "alipay_cert_sn" => "a359aaadd01ceca03dbc07537da539b9",
            "sign" => "tVD05vdQ8K0OOYTEcqHN6LdCeIpJkgrZGYCA4Gb4o/d4rTnmv0DHj40BGdKAfH60ZnssRPR/rWvV+BvptVUOzecA8IGNZ8re3c/Dd0OOKT5v43XM6fczzD6JLdluwNAiGwxHwqO2u/0j6zxwGeQCD/ytRM+Ee2DmNKECWWt4R9jxR2mt9vYHW6GuWT3a9TrpqOaj3cLV4siVPCK9DyKiE6TGH0qu8kRaibcMDCu11JCzAl3nCLInwk8KYtg1GSXdB7PLkcIQ8CMA/kgbMJVM5MlmRs6k0TRVKuRoJJOmEjsJgJenSo+YMl2L8sXd/ljWd1XtgbfQD4uFZHzKnylp0A==",
        ];

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($response)));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::alipay()->close('1623161325');

        self::assertEqualsCanonicalizing($response['alipay_trade_close_response'], $result->all());
    }

    public function testVerifyReturnResponse()
    {
        $url = 'http://127.0.0.1:8000/alipay/verify?charset=utf-8&out_trade_no=yansongda-1622986519&method=alipay.trade.page.pay.return&total_amount=0.01&sign=oSazH3ZnzPQBGfJ8piYuri0E683D7bEKtd1NPcuYctvCEiRWP1QBVWma3hwoTLc19KdXbMGGZcOS5UvtlWwIvcK3oqkuRkFOwcRRmyF0UScmdHrTEPO9VwcaEWPK9Hy%2BTSlYrlnfCae1zlDo4vvNojFZf%2BduaaYCGS2L4Q55atloeztOPsZTNSYI7Jy0rrQcOaAWL7F9aJNqFPW6WkWL31w6HwDHcRSEQzD9C9YTsRkQ7khPHFEw8CHSYp5h8XOq%2BfE0yRDAEEw2pxYYC5QhCtbqVjLdfFXp792cTRd31IB6iAznnDvOATZVgulpC0Z6MV0k0MInL2CarbuO5SZfRg%3D%3D&trade_no=2021060622001498120501382075&auth_app_id=2016082000295641&version=1.0&app_id=2016082000295641&sign_type=RSA2&seller_id=2088102172237210&timestamp=2021-06-06+21%3A35%3A50';
        parse_str(parse_url($url)['query'], $query);
        $request = new ServerRequest('GET', $url);
        $request = $request->withQueryParams($query);

        $result = Pay::alipay()->callback($request);
        self::assertNotEmpty($result->all());

        $result = Pay::alipay()->callback($query);
        self::assertNotEmpty($result->all());
    }

    public function testMergeCommonPlugins()
    {
        Pay::config([]);
        $plugins = [FooPluginStub::class];

        self::assertEquals(array_merge(
            [PreparePlugin::class],
            $plugins,
            [RadarSignPlugin::class],
            [LaunchPlugin::class, ParserPlugin::class],
        ), Pay::alipay()->mergeCommonPlugins($plugins));
    }
}
