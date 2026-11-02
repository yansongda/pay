<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Currency\CurrencyPayPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new CurrencyPayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'env' => 0,
        'user_ip' => '127.0.0.1',
        'amount' => 100,
        'order_id' => 'ORDER_001',
        'payitem' => 'coin*10',
        'remark' => 'purchase coins',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/currency_pay');
    expect($payload->get('openid'))->toEqual('test_openid');
    expect($payload->get('env'))->toEqual(0);
    expect($payload->get('user_ip'))->toEqual('127.0.0.1');
    expect($payload->get('amount'))->toEqual(100);
    expect($payload->get('order_id'))->toEqual('ORDER_001');
    expect($payload->get('payitem'))->toEqual('coin*10');
    expect($payload->get('remark'))->toEqual('purchase coins');
});

it('沙箱环境', function () {
    $plugin = new CurrencyPayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'env' => 1,
        'user_ip' => '127.0.0.1',
        'amount' => 100,
        'order_id' => 'ORDER_001',
        'payitem' => 'coin*10',
        'remark' => 'purchase',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toEqual(1);
});

it('默认值', function () {
    $plugin = new CurrencyPayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'amount' => 100,
        'order_id' => 'ORDER_001',
        'payitem' => 'coin*10',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toBeNull();
    expect($payload->get('user_ip'))->toBeNull();
    expect($payload->get('remark'))->toBeNull();
});

