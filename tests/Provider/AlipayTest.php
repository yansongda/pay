<?php

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\VerifySignaturePlugin;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Pay\Tests\TestCase;

class AlipayTest extends TestCase
{
    public function testShortcutNotFound()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_NOT_FOUND);

        Pay::alipay()->foo();
    }

    public function testShortcutIncompatible()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_NOT_FOUND);

        Pay::alipay()->foo();
    }

    public function testWeb()
    {
        $result = Pay::alipay()->web([
            'out_trade_no' => 'web'.time(),
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - 01',
            '_return_rocket' => true,
        ]);

        $radar = $result->getRadar();

        // 支付宝参数里有实时时间，导致签名不一样，这里只验证签名之前的部分
        $body1 = 'app_id=9021000122682882&method=alipay.trade.page.pay&format=JSON&return_url=https%3A%2F%2Fpay.yansongda.cn&charset=utf-8&sign_type=RSA2&timestamp=';
        $body2 = '&version=1.0&notify_url=https%3A%2F%2Fpay.yansongda.cn&app_cert_sn=e90dd23a37c5c7b616e003970817ff82&alipay_root_cert_sn=687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6&biz_content=%7B%22product_code%22%3A%22FAST_INSTANT_TRADE_PAY%22%2C%22out_trade_no';
        $body3= 'total_amount%22%3A%220.01%22%2C%22subject%22%3A%22yansongda+%5Cu6d4b%5Cu8bd5+-+01%22%7D&sign=';

        self::assertStringContainsString($body1, (string) $radar->getBody());
        self::assertStringContainsString($body2, (string) $radar->getBody());
        self::assertStringContainsString($body3, (string) $radar->getBody());
        self::assertEquals('POST', $radar->getMethod());
    }

    public function testWebGet()
    {
        $result = Pay::alipay()->web([
            '_method' => 'get',
            'out_trade_no' => 'web'.time(),
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - 01',
            '_return_rocket' => true,
        ]);

        $radar = $result->getRadar();

        // 支付宝参数里有实时时间，导致签名不一样，这里只验证签名之前的部分
        $body1 = 'app_id=9021000122682882&method=alipay.trade.page.pay&format=JSON&return_url=https%3A%2F%2Fpay.yansongda.cn&charset=utf-8&sign_type=RSA2&timestamp=';
        $body2 = '&version=1.0&notify_url=https%3A%2F%2Fpay.yansongda.cn&app_cert_sn=e90dd23a37c5c7b616e003970817ff82&alipay_root_cert_sn=687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6&biz_content=%7B%22product_code%22%3A%22FAST_INSTANT_TRADE_PAY%22%2C%22out_trade_no';
        $body3= 'total_amount%22%3A%220.01%22%2C%22subject%22%3A%22yansongda+%5Cu6d4b%5Cu8bd5+-+01%22%7D&sign=';

        self::assertStringContainsString($body1, (string) $radar->getBody());
        self::assertStringContainsString($body2, (string) $radar->getBody());
        self::assertStringContainsString($body3, (string) $radar->getBody());
        self::assertEquals('GET', $radar->getMethod());
    }

    public function testH5()
    {
        $result = Pay::alipay()->h5([
            'out_trade_no' => 'web'.time(),
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - 01',
            'quit_url' => 'https://yansongda.cn',
            '_return_rocket' => true,
        ]);

        $radar = $result->getRadar();

        // 支付宝参数里有实时时间，导致签名不一样，这里只验证签名之前的部分
        $body1 = 'app_id=9021000122682882&method=alipay.trade.wap.pay&format=JSON&return_url=https%3A%2F%2Fpay.yansongda.cn&charset=utf-8&sign_type=RSA2&timestamp=';
        $body2 = '&version=1.0&notify_url=https%3A%2F%2Fpay.yansongda.cn&app_cert_sn=e90dd23a37c5c7b616e003970817ff82&alipay_root_cert_sn=687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6&biz_content=%7B%22out_trade_no%22%3A%22web';
        $body3= 'total_amount%22%3A%220.01%22%2C%22subject%22%3A%22yansongda+%5Cu6d4b%5Cu8bd5+-+01%22%2C%22quit_url%22%3A%22https%3A%5C%2F%5C%2Fyansongda.cn%22%7D&sign=';

        self::assertStringContainsString($body1, (string) $radar->getBody());
        self::assertStringContainsString($body2, (string) $radar->getBody());
        self::assertStringContainsString($body3, (string) $radar->getBody());
        self::assertEquals('POST', $radar->getMethod());
    }

    public function testH5Get()
    {
        $result = Pay::alipay()->h5([
            '_method' => 'get',
            'out_trade_no' => 'web'.time(),
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - 01',
            'quit_url' => 'https://yansongda.cn',
            '_return_rocket' => true,
        ]);

        $radar = $result->getRadar();

        // 支付宝参数里有实时时间，导致签名不一样，这里只验证签名之前的部分
        $body1 = 'app_id=9021000122682882&method=alipay.trade.wap.pay&format=JSON&return_url=https%3A%2F%2Fpay.yansongda.cn&charset=utf-8&sign_type=RSA2&timestamp=';
        $body2 = '&version=1.0&notify_url=https%3A%2F%2Fpay.yansongda.cn&app_cert_sn=e90dd23a37c5c7b616e003970817ff82&alipay_root_cert_sn=687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6&biz_content=%7B%22out_trade_no%22%3A%22web';
        $body3= 'total_amount%22%3A%220.01%22%2C%22subject%22%3A%22yansongda+%5Cu6d4b%5Cu8bd5+-+01%22%2C%22quit_url%22%3A%22https%3A%5C%2F%5C%2Fyansongda.cn%22%7D&sign=';

        self::assertStringContainsString($body1, (string) $radar->getBody());
        self::assertStringContainsString($body2, (string) $radar->getBody());
        self::assertStringContainsString($body3, (string) $radar->getBody());
        self::assertEquals('GET', $radar->getMethod());
    }

    public function testApp()
    {
        $result = Pay::alipay()->app([
            'out_trade_no' => 'web'.time(),
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - 01',
        ]);

        // 支付宝参数里有实时时间，导致签名不一样，这里只验证签名之前的部分
        $body1 = 'app_id=9021000122682882&method=alipay.trade.app.pay&format=JSON&return_url=https%3A%2F%2Fpay.yansongda.cn&charset=utf-8&sign_type=RSA2';
        $body2 = '&version=1.0&notify_url=https%3A%2F%2Fpay.yansongda.cn&app_cert_sn=e90dd23a37c5c7b616e003970817ff82&alipay_root_cert_sn=687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6&biz_content=%7B%22out_trade_no%22%3A%22web';
        $body3= 'total_amount%22%3A%220.01%22%2C%22subject%22%3A%22yansongda+%5Cu6d4b%5Cu8bd5+-+01%22%7D&sign=';

        self::assertStringContainsString($body1, (string) $result->getBody());
        self::assertStringContainsString($body2, (string) $result->getBody());
        self::assertStringContainsString($body3, (string) $result->getBody());
    }

    public function testScan()
    {
        $response = [
            "alipay_trade_precreate_response" => [
                "code" => "10000",
                "msg" => "Success",
                "out_trade_no" => "1704093802",
                "qr_code" => "https://qr.alipay.com/bax07651xvtprxfkmxyf00a9",
            ],
            "sign" => "Mz0jF7DeQ6ogyle/5P6cxqbezmR9RNkuJFNIiH4bPrcHjwnaNsb3Ad5PAFc7xERDHOvfdWkNzx8++Ov+cAhfqIcKWNLyL7UFiRIeL1LoLku2K0rcAXeuxm9KKjYaEl3lr0JFQE/zUElfDcQn3s9ujNFkkMNOcPgQwbSPlEXja0a4syG76FnTknsITHONIbCCsIWnHMuf95cq8zkgjMUOlfVOL8l4Iv0n9n2r9H1OJKv+2GmDp6ntc9xAhrIlO+f5urp1N+LzO9lDx5r1HIjqlSiemNZAdQveQjRuOtx/FM4KhFvn3312Xv7XuKW9iqNnBSwXOEAuUXghXcqD8xl05g==",
        ];

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($response)));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::alipay()->scan([
            'out_trade_no' => time(),
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - 01',
            '_return_rocket' => true,
        ]);

        // 支付宝参数里有实时时间，导致签名不一样，这里只验证签名之前的部分
        $body1 = 'app_id=9021000122682882&method=alipay.trade.precreate&format=JSON&return_url=https%3A%2F%2Fpay.yansongda.cn&charset=utf-8&sign_type=RSA2&timestamp=';
        $body2 = '&version=1.0&notify_url=https%3A%2F%2Fpay.yansongda.cn&app_cert_sn=e90dd23a37c5c7b616e003970817ff82&alipay_root_cert_sn=687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6&biz_content=%7B%22out_trade_no';
        $body3 = 'total_amount%22%3A%220.01%22%2C%22subject%22%3A%22yansongda+%5Cu6d4b%5Cu8bd5+-+01%22%7D&sign=';

        self::assertStringContainsString($body1, (string) $result->getRadar()->getBody());
        self::assertStringContainsString($body2, (string) $result->getRadar()->getBody());
        self::assertStringContainsString($body3, (string) $result->getRadar()->getBody());
        self::assertEqualsCanonicalizing($response['alipay_trade_precreate_response'], $result->getDestination()->except('_sign')->all());
    }

    public function testTransfer()
    {
        $response = [
            "alipay_fund_trans_uni_transfer_response" => [
                "code" => "10000",
                "msg" => "Success",
                "order_id" => "20231226110070000002150000683137",
                "out_biz_no" => "2023122621450001",
                "pay_fund_order_id" => "20231226110070001502150000685481",
                "status" => "SUCCESS",
                "trans_date" => "2023-12-26 22:11:45",
            ],
            "sign" => "exg0CUSgsRvI+q/Qqyu+MJ17ao4+vnEUMRE4YNbN2H3K6iX3xBcZv9jTt6m6c9JLZIifbqkZU13PLa4zy1MaQnQKg676wbqpN7ybEVL7LMzAgXUFm3Dc0XL1minPie2XOtwIgEecoPwpEqvqjjdTXfaE7fT6ZLxFLMMlPAESGwDDnKQVUmWs/8oq/EdPDNtVMmoVbF4o9zizyHw/QHVpLYvt0DHNCZRLhY85V99W6CrHjkNTB1QzEb1vCe3okVT3UAq26sxpu46R5l3n0xKJiYrucs8Y6CEWmayTKmZou7WQdgKQJHC0x0OIN58zWBkAFwz9ZAGON/WO3YHWq6mi5A==",
        ];

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($response)));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::alipay()->transfer([
            'out_biz_no' => '2023122621450001',
            'trans_amount' => '0.01',
            'product_code' => 'TRANS_ACCOUNT_NO_PWD',
            'biz_scene' => 'DIRECT_TRANSFER',
            'payee_info' => [
                'identity' => 'ifvlwp1413@sandbox.com',
                'identity_type' => 'ALIPAY_LOGON_ID',
                'name' => 'ifvlwp1413'
            ],
            '_return_rocket' => true,
        ]);

        // 支付宝参数里有实时时间，导致签名不一样，这里只验证签名之前的部分
        $body1 = 'app_id=9021000122682882&method=alipay.fund.trans.uni.transfer&format=JSON&return_url=https%3A%2F%2Fpay.yansongda.cn&charset=utf-8&sign_type=RSA2&timestamp=';
        $body2 = '&version=1.0&notify_url=https%3A%2F%2Fpay.yansongda.cn&app_cert_sn=e90dd23a37c5c7b616e003970817ff82&alipay_root_cert_sn=687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6&biz_content=%7B%22biz_scene%22%3A%22DIRECT_TRANSFER%22%2C%22product_code%22%3A%22TRANS_ACCOUNT_NO_PWD%22%2C%22out_biz_no%22%3A%222023122621450001%22%2C%22trans_amount%22%3A%220.01%22%2C%22payee_info%22%3A%7B%22identity%22%3A%22ifvlwp1413%40sandbox.com%22%2C%22identity_type%22%3A%22ALIPAY_LOGON_ID%22%2C%22name%22%3A%22ifvlwp1413%22%7D%7D&sign=';

        self::assertStringContainsString($body1, (string) $result->getRadar()->getBody());
        self::assertStringContainsString($body2, (string) $result->getRadar()->getBody());
        self::assertEqualsCanonicalizing($response['alipay_fund_trans_uni_transfer_response'], $result->getDestination()->except('_sign')->all());
    }

    public function testQueryDefault()
    {
        $response = [
            "alipay_trade_query_response" => [
                "code" => "10000",
                "msg" => "Success",
                "buyer_logon_id" => "ifv***@sandbox.com",
                "buyer_pay_amount" => "0.00",
                "buyer_user_id" => "2088722003899169",
                "buyer_user_type" => "PRIVATE",
                "invoice_amount" => "0.00",
                "out_trade_no" => "1703141270",
                "point_amount" => "0.00",
                "receipt_amount" => "0.00",
                "send_pay_date" => "2023-12-21 14:48:36",
                "total_amount" => "0.01",
                "trade_no" => "2023122122001499160501586202",
                "trade_status" => "TRADE_SUCCESS",
            ],
            "sign" => "WBz5iEFVhP99SRqHAaUi6KD+6u4xUxOgLAJ989gxByd79pa9bhHfQ0EFO/78YU3TuoqNvUBbHZ7LPxP+OPQFTUtHa5JF2pz+EfgBkYOnBPW+YGz6arGqmAPBy9I+ltJxKNKq4G7ehPG0gbtQQcVqqIR9vDylitmlGIIe+YKfNbEi+vPNkQ3HXLsu3lXKGqB21XSYb/NdxneALsVOowVqgU2SSR/+5TcUzCuW5VA/LWKnpXZEDdE1HTgUFqvqrYtLoVfmXO41oKZdrR3t4/rbV64YlWR4vPSuELuC4gLdXdd63PaOmdIo/5TxI26379ZC8IfhcBiS/KO3PYm1dbgpIg==",
        ];

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($response)));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::alipay()->query(['out_trade_no' => '1703141270']);

        self::assertEqualsCanonicalizing($response['alipay_trade_query_response'], $result->except('_sign')->all());
    }

    public function testQueryTransfer()
    {
        $response = [
            "alipay_fund_trans_common_query_response" => [
                "code" => "10000",
                "msg" => "Success",
                "order_id" => "20231226110070000002150000683137",
                "out_biz_no" => "2023122621450001",
                "pay_date" => "2023-12-26 22:11:45",
                "pay_fund_order_id" => "20231226110070001502150000685481",
                "status" => "SUCCESS",
                "trans_amount" => "0.01",
            ],
            "sign" => "AVEw2M/E95HJvcUVS05s/ABD96Hlw0IlGyjz36IjFMmb2u0Qviz/ZSBGnSdW4XH4Nda80h4hmiuslp7vnydeZKiyMUMms1wq8YZGYCjrBPs1pj898wPT22foVWEIAmwZYQ1ixJtmycYd8wZfg0y+fuLiSYifsik4OyQ8SGam1k0RqB1Qje0v8WKLtsrZszw0zDp9vYbPuCTLkgmT0gGRxHoUOP2JLfpK/uJs54tECVF9FUEVJmeBM8TvTxgMPB0b32MOzOtI1JB8qE/Gn7RaMbTVrQQZNCSEHhjhaCvHoBo3xVbx8Rcq6Xl2Nf0N8uEmK6UQqqLh//IW4nWs0T4HHQ==",
        ];

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($response)));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::alipay()->query([
            'out_biz_no' => '2023122621450001',
            'product_code' => 'TRANS_ACCOUNT_NO_PWD',
            'biz_scene' => 'DIRECT_TRANSFER',
            '_action' => 'transfer',
            '_return_rocket' => true,
        ]);

        $body1 = 'app_id=9021000122682882&method=alipay.fund.trans.common.query&format=JSON&return_url=https%3A%2F%2Fpay.yansongda.cn&charset=utf-8&sign_type=RSA2&timestamp=';
        $body2 = '&version=1.0&notify_url=https%3A%2F%2Fpay.yansongda.cn&app_cert_sn=e90dd23a37c5c7b616e003970817ff82&alipay_root_cert_sn=687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6&biz_content=%7B%22out_biz_no%22%3A%222023122621450001%22%2C%22product_code%22%3A%22TRANS_ACCOUNT_NO_PWD%22%2C%22biz_scene%22%3A%22DIRECT_TRANSFER%22%7D&sign=';

        self::assertStringContainsString($body1, (string) $result->getRadar()->getBody());
        self::assertStringContainsString($body2, (string) $result->getRadar()->getBody());
        self::assertEqualsCanonicalizing($response['alipay_fund_trans_common_query_response'], $result->getDestination()->except('_sign')->all());
    }

    public function testQueryRefund()
    {
        $response = [
            "alipay_trade_fastpay_refund_query_response" => [
                "code" => "10000",
                "msg" => "Success",
                "out_request_no" => "1703141270",
                "out_trade_no" => "1703141270",
                "refund_amount" => "0.01",
                "refund_status" => "REFUND_SUCCESS",
                "total_amount" => "0.01",
                "trade_no" => "2023122122001499160501586202",
            ],
            "sign" => "fifFt09uvYUz5SEC24ZrJZOV8am4ZLTjMmDn2WTEZ5hcxmf8ZpBwls8YFFUeJjCCy9CEnG5xMVKZemg23D/OBlqQVNxmGRYvV5f/hSeUVUoaTbsGodBkSeuKL9rxfjU0srSNolICxwsNZ7l3ZzRLATrQCpn/ObIen1M2x7aVeGHjpyDpYd4oMm7jVnsWQlR+03Atcvj2EbkjcuK7pf0pWV7R75SO2/sKCr+8h7SRoBZeQKa7pyGe70u9vxtVRidZ6EMMLRWpQ0MEt+40FKCUUKE/ATEvg9gkAO3J8xUN6HwCchz+1RAa5HGLBgQ15lTDw4PdfL+6fJkdgxhIvulNsw==",
        ];

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->twice()->andReturn(
            new Response(200, [], json_encode($response)), new Response(200, [], json_encode($response))
        );
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::alipay()->query([
            'out_trade_no' => '1703141270',
            'out_request_no' => '1703141270',
            '_action' => 'refund'
        ]);
        self::assertEqualsCanonicalizing($response['alipay_trade_fastpay_refund_query_response'], $result->except('_sign')->all());

        $result1 = Pay::alipay()->query([
            'out_trade_no' => '1703141270',
            'out_request_no' => '1703141270',
        ]);
        self::assertEqualsCanonicalizing($response['alipay_trade_fastpay_refund_query_response'], $result1->except('_sign')->all());
    }

    public function testClose()
    {
        $response = [
            "alipay_trade_close_response" => [
                "code" => "10000",
                "msg" => "Success",
                "out_trade_no" => "1703226647",
                "trade_no" => "2023122222001499160501602106",
            ],
            "sign" => "BFKYag0TR6czgQ8MmRKZF9h+S0Vh+W+44zQA8BVzPca3hRirhETZEkuVQwBLyWwIXHnXDEulGoYpBouL2gMXB7wVW05XvCQ/6A2NTHybtoetF7MFKuyhdvj2+5kDq3hY4gOeIXBvBLSvikXr6pP0w5kKMG59z7cMITsDR+q2sEGKxO9wKTLaKmShaih6+W/hX7VwN+z5ZI90o6M5EFZeVifYLGCfbS0vi0ZOL3n2shglCKzKmxxND/RrS8f/cE51s++/A6bJDuP2aZj9ZE+tPbESGNtOxOdf1G99CFq6Kjcg1PR71MN8PXDbIIRsegenngDQz9rl2EBpgzvtGrFW2Q==",
        ];

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($response)));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::alipay()->close(['out_trade_no' => '1703226647']);

        self::assertEqualsCanonicalizing($response['alipay_trade_close_response'], $result->except('_sign')->all());
    }

    public function testRefund()
    {
        $response = [
            "alipay_trade_refund_response" => [
                "code" => "10000",
                "msg" => "Success",
                "buyer_logon_id" => "ifv***@sandbox.com",
                "buyer_user_id" => "2088722003899169",
                "fund_change" => "Y",
                "gmt_refund_pay" => "2023-12-22 14:19:12",
                "out_trade_no" => "1703141270",
                "refund_fee" => "0.01",
                "send_back_fee" => "0.00",
                "trade_no" => "2023122122001499160501586202",
            ],
            "sign" => "LPvOTnBZH6ZnPSbHDnJrsmc3v6M6HFHZt2kVDC0gP1oIqOL3nThcOqth4Cn8PfdXOOpN57IrU6J3XmaI2hvratXLdmdbiiyNccsyXKB5KQOw9jR72tC0r0AT5VXw3BQJ+Jgnaapd6Ud7rkmWTLADv4PQh1pvJSMWto+Auc1CL+Oq3ERDhfMRqLUsrDUr/ogQAwkIFe8AHL7bGlrgLH7IpKEVrf436NHgBYMCHFI/4Gzi1dJyFXrNyD7x5FnN9qIMNhMeMq/fH2iCe8JTGUIdTk7S4+L0rSr+6ZAbj6JO7rumemAnkfS3h11AxaLHNLnxNwPVaYHlw9HiJErHeC/Z0w==",
        ];

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($response)));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::alipay()->refund(['out_trade_no' => '1703141270', 'refund_amount' => '0.01',]);

        self::assertEqualsCanonicalizing($response['alipay_trade_refund_response'], $result->except('_sign')->all());
    }

    public function testCallback()
    {
        $url = 'http://127.0.0.1:8000/alipay/verify?charset=utf-8&out_trade_no=1703141270&method=alipay.trade.page.pay.return&total_amount=0.01&sign=RJzbs5y7I41BO9UPnCdq7oWgoInyjELi9Qj6D%2BLAZXVpHTedemAHfVUowuF9iuznGZLxU6Xv1L3ZkzTGxmIfvzontCZNb0%2BRROqiT41lX91VYd6j4ZcOn8zsvlCdQSVHmYNJi%2Bw%2F40uHxo1ufRwHxBNtQKsoJCYk5VtZ92pQFvVyE5wPPT6Nolww5WlCAPxcWNby8VAiWT%2Bd2yxmFm8vZ6yj5rsLHTR72O76TkEXzOEex6e36Zf8M9YXww7RQbflMfk9eURPHW%2FoQq4hZr%2FlX7%2FO1nT5vdT4UVFai4V18Xm1KspBun8outJxqlWMIKVxGsYhIH1E79ORt4wQA7PG1g%3D%3D&trade_no=2023122122001499160501586202&auth_app_id=9021000122682882&version=1.0&app_id=9021000122682882&sign_type=RSA2&seller_id=2088721003899159&timestamp=2023-12-21+14%3A48%3A44';
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
        Pay::config();
        $plugins = [FooPluginStub::class];

        self::assertEquals(array_merge(
            [StartPlugin::class],
            $plugins,
            [FormatPayloadBizContentPlugin::class, AddPayloadSignaturePlugin::class, AddRadarPlugin::class, VerifySignaturePlugin::class, ResponsePlugin::class, ParserPlugin::class],
        ), Pay::alipay()->mergeCommonPlugins($plugins));
    }

    public function testSuccess()
    {
        $result = Pay::alipay()->success();

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertEquals('success', (string) $result->getBody());
    }
}
