<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Alipay\CallbackPlugin;

use function Yansongda\Artful\filter_params;

uses(CallbackPluginTestHelpers::class);

it('return callback', function () {
    $plugin = new CallbackPlugin();
    $url = 'http://127.0.0.1:8000/alipay/verify?charset=utf-8&out_trade_no=1703141270&method=alipay.trade.page.pay.return&total_amount=0.01&sign=RJzbs5y7I41BO9UPnCdq7oWgoInyjELi9Qj6D%2BLAZXVpHTedemAHfVUowuF9iuznGZLxU6Xv1L3ZkzTGxmIfvzontCZNb0%2BRROqiT41lX91VYd6j4ZcOn8zsvlCdQSVHmYNJi%2Bw%2F40uHxo1ufRwHxBNtQKsoJCYk5VtZ92pQFvVyE5wPPT6Nolww5WlCAPxcWNby8VAiWT%2Bd2yxmFm8vZ6yj5rsLHTR72O76TkEXzOEex6e36Zf8M9YXww7RQbflMfk9eURPHW%2FoQq4hZr%2FlX7%2FO1nT5vdT4UVFai4V18Xm1KspBun8outJxqlWMIKVxGsYhIH1E79ORt4wQA7PG1g%3D%3D&trade_no=2023122122001499160501586202&auth_app_id=9021000122682882&version=1.0&app_id=9021000122682882&sign_type=RSA2&seller_id=2088721003899159&timestamp=2023-12-21+14%3A48%3A44';
    parse_str(parse_url($url)['query'], $query);
    $request = new ServerRequest('GET', $url);
    $request = $request->withQueryParams($query);

    $rocket = new Rocket();
    $rocket->setParams($request->getQueryParams());

    $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

    expect($result->getPayload()->all())->not->toBeEmpty();
});

it('return callback multi config', function () {
    $plugin = new CallbackPlugin();
    $url = 'http://127.0.0.1:8000/alipay/verify?_config=default&charset=utf-8&out_trade_no=1703141270&method=alipay.trade.page.pay.return&total_amount=0.01&sign=RJzbs5y7I41BO9UPnCdq7oWgoInyjELi9Qj6D%2BLAZXVpHTedemAHfVUowuF9iuznGZLxU6Xv1L3ZkzTGxmIfvzontCZNb0%2BRROqiT41lX91VYd6j4ZcOn8zsvlCdQSVHmYNJi%2Bw%2F40uHxo1ufRwHxBNtQKsoJCYk5VtZ92pQFvVyE5wPPT6Nolww5WlCAPxcWNby8VAiWT%2Bd2yxmFm8vZ6yj5rsLHTR72O76TkEXzOEex6e36Zf8M9YXww7RQbflMfk9eURPHW%2FoQq4hZr%2FlX7%2FO1nT5vdT4UVFai4V18Xm1KspBun8outJxqlWMIKVxGsYhIH1E79ORt4wQA7PG1g%3D%3D&trade_no=2023122122001499160501586202&auth_app_id=9021000122682882&version=1.0&app_id=9021000122682882&sign_type=RSA2&seller_id=2088721003899159&timestamp=2023-12-21+14%3A48%3A44';
    parse_str(parse_url($url)['query'], $query);
    $request = new ServerRequest('GET', $url);
    $request = $request->withQueryParams($query);

    $rocket = new Rocket();
    $rocket->setParams($request->getQueryParams());

    $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

    expect($result->getPayload()->all())->not->toBeEmpty();
});

it('notify callback include plus', function () {
    $plugin = new CallbackPlugin();
    $post = [
        'gmt_create' => '2023-12-21 16:26:32',
        'charset' => 'utf-8',
        'gmt_payment' => '2023-12-21 16:26:43',
        'notify_time' => '2023-12-21 16:26:45',
        'subject' => 'yansongda+测试 - 1',
        'sign' => 'Rs7SszD15WqbdJsGZPN+HpCBcQzBCkJx0ccNeg1rpUxyTVA1ps2vJ4sLhaa7O+lliTj6N1MSGqc8cK6JSs7nPg731RAOfneirtrqrlW2u0m15kWAKrVjgPGLUMtK/eT15e/6NcxsWra/bsZaan+nFDzO8xceOwy96W+qVUtTPfBRBp9Zjryi5oIONOifGMALD284YbNC3qq2eyqVysF+zgk+/MtuHk2Eh/fL7UbClHQXQ2hD686Dt8bR949TNMbkWCYXstmjVBO55qF8xhaoF/b5zAe5/O/13g/QlwDXBcF3XwJpbFWrBehoFFCnJhR/xXZ0E+D2Vsw1oAQ3l+R2dQ==',
        'buyer_id' => '2088722003899169',
        'invoice_amount' => '0.01',
        'version' => '1.0',
        'notify_id' => '2023122101222162644199160501632046',
        'fund_bill_list' => '[{"amount":"0.01","fundChannel":"ALIPAYACCOUNT"}]',
        'notify_type' => 'trade_status_sync',
        'out_trade_no' => '1703147160',
        'total_amount' => '0.01',
        'trade_status' => 'TRADE_SUCCESS',
        'trade_no' => '2023122122001499160501589436',
        'auth_app_id' => '9021000122682882',
        'receipt_amount' => '0.01',
        'point_amount' => '0.00',
        'app_id' => '9021000122682882',
        'buyer_pay_amount' => '0.01',
        'sign_type' => 'RSA2',
        'seller_id' => '2088721003899159',
    ];

    $rocket = new Rocket();
    $rocket->setParams($post);

    $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

    expect($result->getPayload()->all())->not->toBeEmpty();
});

it('notify callback with v3 cert tenant', function () {
    $plugin = new CallbackPlugin();
    $form = $this->makeSignedForm();

    $rocket = (new Rocket())->setParams(array_merge($form, ['_config' => 'alipay-v3']));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getDirection())->toBe(NoHttpRequestDirection::class);
    expect($result->getPayload()->except('_config')->all())->toBe($form);
});

it('notify callback with v3 cert tenant tampered params', function () {
    $plugin = new CallbackPlugin();
    $form = $this->makeSignedForm();
    $form['total_amount'] = '999.00';

    $rocket = (new Rocket())->setParams(array_merge($form, ['_config' => 'alipay-v3']));

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
});

it('notify callback with v3 cert tenant missing sign', function () {
    $plugin = new CallbackPlugin();
    $form = $this->makeSignedForm();
    $form['sign'] = '';

    $rocket = (new Rocket())->setParams(array_merge($form, ['_config' => 'alipay-v3']));

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_EMPTY);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
});

trait CallbackPluginTestHelpers
{
    private function makeSignedForm(): array
    {
        $form = [
            'app_id' => 'alipay_v3_test_app_id',
            'trade_no' => '2023122122001499160501589436',
            'out_trade_no' => '1703147160',
            'total_amount' => '0.01',
            'trade_status' => 'TRADE_SUCCESS',
            'sign_type' => 'RSA2',
        ];
    
        $value = filter_params($form, fn ($k, $v) => '' !== $v && 'sign' != $k && 'sign_type' != $k)->sortKeys()->toString();
    
        openssl_sign($value, $sign, openssl_pkey_get_private(file_get_contents(__DIR__.'/../../Cert/alipay-v3/app_secret_test.pem')), OPENSSL_ALGO_SHA256);
    
        $form['sign'] = base64_encode($sign);
    
        return $form;
    }
}
