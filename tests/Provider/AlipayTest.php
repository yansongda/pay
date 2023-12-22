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
use Yansongda\Pay\Plugin\Alipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\AddSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\FormatBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\VerifySignaturePlugin;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Pay\Tests\TestCase;

class AlipayTest extends TestCase
{
    public function testShortcutNotFound()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::SHORTCUT_NOT_FOUND);

        Pay::alipay()->foo();
    }

    public function testShortcutIncompatible()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::SHORTCUT_NOT_FOUND);

        Pay::alipay()->foo();
    }

    public function testFindDefault()
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

        $result = Pay::alipay()->query('1703141270');

        self::assertEqualsCanonicalizing($response['alipay_trade_query_response'], $result->except('_sign')->all());
    }

    public function testFindTransfer()
    {
        $response = [
            "alipay_fund_trans_common_query_response" => [
                "code" => "10000",
                "msg" => "Success",
                "order_id" => "20231220110070000002150000657610",
                "out_biz_no" => "2023122022560000",
                "pay_date" => "2023-12-20 22:56:33",
                "pay_fund_order_id" => "20231220110070001502150000660902",
                "status" => "SUCCESS",
                "trans_amount" => "0.01",
            ],
            "sign" => "eITxP5fZiJPB2+vZb90IRkv2iARxeNx/6Omxk7FStqflhG5lMoCvGjo2FZ6Szo1bGBMBReazZuqLaqsgomWAUO9onMVurB3enLbRvwUlpE7XEZaxk/sJYjgc2Y7pIAenvnLL9PEAiXmvUvuinUlvS9J2r1XysC0p/2wu7kEJ/GgZpFDIIYY9mdM6U1rGbi+RvirQXtQHmaEuuJWLA75NR1bvfG3L8znzW9xz1kOQqOWsQmD/bF1CDWbozNLwLCUmClRJz0Fj4mUYRF0zbW2VP8ZgHu1YvVKJ2+dWC9b+0o94URk7psIpc5NjiOM9Jsn6aoC2CfrJ/sqFMRCkYWzw6A==",
        ];

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($response)));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::alipay()->query(['out_biz_no' => '202209032319', '_action' => 'transfer']);
        self::assertEqualsCanonicalizing($response['alipay_fund_trans_common_query_response'], $result->except('_sign')->all());
    }

    public function testFindRefund()
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
        self::assertEqualsCanonicalizing($response['alipay_trade_fastpay_refund_query_response'], $result->except('_sign')->all());
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
            [FormatBizContentPlugin::class, AddSignaturePlugin::class, AddRadarPlugin::class, VerifySignaturePlugin::class, ResponsePlugin::class, ParserPlugin::class],
        ), Pay::alipay()->mergeCommonPlugins($plugins));
    }

    public function testSuccess()
    {
        $result = Pay::alipay()->success();

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertEquals('success', (string) $result->getBody());
    }
}
