<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Pay\Plugin\Alipay\CallbackPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class CallbackPluginTest extends TestCase
{
    private $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CallbackPlugin();
    }

    public function testReturnCallback()
    {
        $url = 'http://127.0.0.1:8000/alipay/verify?charset=utf-8&out_trade_no=yansongda-1622986519&method=alipay.trade.page.pay.return&total_amount=0.01&sign=oSazH3ZnzPQBGfJ8piYuri0E683D7bEKtd1NPcuYctvCEiRWP1QBVWma3hwoTLc19KdXbMGGZcOS5UvtlWwIvcK3oqkuRkFOwcRRmyF0UScmdHrTEPO9VwcaEWPK9Hy%2BTSlYrlnfCae1zlDo4vvNojFZf%2BduaaYCGS2L4Q55atloeztOPsZTNSYI7Jy0rrQcOaAWL7F9aJNqFPW6WkWL31w6HwDHcRSEQzD9C9YTsRkQ7khPHFEw8CHSYp5h8XOq%2BfE0yRDAEEw2pxYYC5QhCtbqVjLdfFXp792cTRd31IB6iAznnDvOATZVgulpC0Z6MV0k0MInL2CarbuO5SZfRg%3D%3D&trade_no=2021060622001498120501382075&auth_app_id=2016082000295641&version=1.0&app_id=2016082000295641&sign_type=RSA2&seller_id=2088102172237210&timestamp=2021-06-06+21%3A35%3A50';
        parse_str(parse_url($url)['query'], $query);
        $request = new ServerRequest('GET', $url);
        $request = $request->withQueryParams($query);

        $rocket = new Rocket();
        $rocket->setParams($request->getQueryParams());

        $result = $this->plugin->assembly($rocket, function ($rocket) {return $rocket;});

        self::assertNotEmpty($result->getPayload()->all());
    }

    public function testReturnCallbackMultiConfig()
    {
        $url = 'http://127.0.0.1:8000/alipay/verify?_config=default&charset=utf-8&out_trade_no=yansongda-1622986519&method=alipay.trade.page.pay.return&total_amount=0.01&sign=oSazH3ZnzPQBGfJ8piYuri0E683D7bEKtd1NPcuYctvCEiRWP1QBVWma3hwoTLc19KdXbMGGZcOS5UvtlWwIvcK3oqkuRkFOwcRRmyF0UScmdHrTEPO9VwcaEWPK9Hy%2BTSlYrlnfCae1zlDo4vvNojFZf%2BduaaYCGS2L4Q55atloeztOPsZTNSYI7Jy0rrQcOaAWL7F9aJNqFPW6WkWL31w6HwDHcRSEQzD9C9YTsRkQ7khPHFEw8CHSYp5h8XOq%2BfE0yRDAEEw2pxYYC5QhCtbqVjLdfFXp792cTRd31IB6iAznnDvOATZVgulpC0Z6MV0k0MInL2CarbuO5SZfRg%3D%3D&trade_no=2021060622001498120501382075&auth_app_id=2016082000295641&version=1.0&app_id=2016082000295641&sign_type=RSA2&seller_id=2088102172237210&timestamp=2021-06-06+21%3A35%3A50';
        parse_str(parse_url($url)['query'], $query);
        $request = new ServerRequest('GET', $url);
        $request = $request->withQueryParams($query);

        $rocket = new Rocket();
        $rocket->setParams($request->getQueryParams());

        $result = $this->plugin->assembly($rocket, function ($rocket) {return $rocket;});

        self::assertNotEmpty($result->getPayload()->all());
    }

    public function testNotifyCallbackIncludePlus()
    {
        $post = [
            "gmt_create" => "2022-09-18 13:29:48",
            "charset" => "utf-8",
            "gmt_payment" => "2022-09-18 13:30:07",
            "notify_time" => "2022-09-18 13:30:09",
            "subject" => "yansongda+测试-1",
            "sign" => "C5gjBgiYDWLwS1HxGQ6D8ZM+sYpnfTKMqe7YkNxqE5cm+AXQqOHi1Pf928meGGvBUQLvIee8ziKmRbxhEgYEcJsgO/X2Zy2PNASLO1Q1b4MfjgGiQqfv2bpLPkfhvftF//Ih79GWj6KAPxyvRq9N0pMTkxv4yDwo637ksbK0WCsh/v90nOdYh1HxZs1pgRAIBcNgr0GPFQJtxjO7nG+ppta3wD5aYuuRxtohRv1LK6ZLbAM45mMf0eUYmKsZBFIaISCVNo0mu+H+GLAJ2Z0aXZ0dti5jVkAtA0eJHmJ0da2VGCGy0wmQFSZ4AxwjSM2LQg7gn4GWMUgvKK8pDzIn6Q==",
            "buyer_id" => "2088102174698127",
            "invoice_amount" => "0.01",
            "version" => "1.0",
            "notify_id" => "2022091800222133008098120532343049",
            "fund_bill_list" => "[{\"amount\":\"0.01\",\"fundChannel\":\"ALIPAYACCOUNT\"}]",
            "notify_type" => "trade_status_sync",
            "out_trade_no" => "web1663478942",
            "total_amount" => "0.01",
            "trade_status" => "TRADE_SUCCESS",
            "trade_no" => "2022091822001498120503050754",
            "auth_app_id" => "2016082000295641",
            "receipt_amount" => "0.01",
            "point_amount" => "0.00",
            "app_id" => "2016082000295641",
            "buyer_pay_amount" => "0.01",
            "sign_type" => "RSA2",
            "seller_id" => "2088102172237210",
        ];

        $rocket = new Rocket();
        $rocket->setParams($post);

        $result = $this->plugin->assembly($rocket, function ($rocket) {return $rocket;});

        self::assertNotEmpty($result->getPayload()->all());
    }
}
