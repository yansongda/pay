<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\CallbackPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class CallbackPluginTest extends TestCase
{
    public function testReturnCallback()
    {
        $url = 'http://127.0.0.1:8000/alipay/verify?charset=utf-8&out_trade_no=yansongda-1622986519&method=alipay.trade.page.pay.return&total_amount=0.01&sign=oSazH3ZnzPQBGfJ8piYuri0E683D7bEKtd1NPcuYctvCEiRWP1QBVWma3hwoTLc19KdXbMGGZcOS5UvtlWwIvcK3oqkuRkFOwcRRmyF0UScmdHrTEPO9VwcaEWPK9Hy%2BTSlYrlnfCae1zlDo4vvNojFZf%2BduaaYCGS2L4Q55atloeztOPsZTNSYI7Jy0rrQcOaAWL7F9aJNqFPW6WkWL31w6HwDHcRSEQzD9C9YTsRkQ7khPHFEw8CHSYp5h8XOq%2BfE0yRDAEEw2pxYYC5QhCtbqVjLdfFXp792cTRd31IB6iAznnDvOATZVgulpC0Z6MV0k0MInL2CarbuO5SZfRg%3D%3D&trade_no=2021060622001498120501382075&auth_app_id=2016082000295641&version=1.0&app_id=2016082000295641&sign_type=RSA2&seller_id=2088102172237210&timestamp=2021-06-06+21%3A35%3A50';
        parse_str(parse_url($url)['query'], $query);
        $request = new ServerRequest('GET', $url);
        $request = $request->withQueryParams($query);

        $rocket = new Rocket();
        $rocket->setParams($request->getQueryParams());

        /* @var CallbackPlugin $plugin */
        $plugin = Pay::get(CallbackPlugin::class);
        $result = $plugin->assembly($rocket, function ($rocket) {return $rocket;});

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

        /* @var CallbackPlugin $plugin */
        $plugin = Pay::get(CallbackPlugin::class);
        $result = $plugin->assembly($rocket, function ($rocket) {return $rocket;});

        self::assertNotEmpty($result->getPayload()->all());
    }
}
