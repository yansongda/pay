<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Currency\CancelCurrencyPayPlugin;
use Yansongda\Supports\Collection;

it('normal', function () {
    $plugin = new CancelCurrencyPayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'env' => 0,
        'order_id' => 'ORDER_001',
        'refund_reason' => 'user requested',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/cancel_currency_pay');
    expect($payload->get('openid'))->toEqual('test_openid');
    expect($payload->get('env'))->toEqual(0);
    expect($payload->get('order_id'))->toEqual('ORDER_001');
    expect($payload->get('refund_reason'))->toEqual('user requested');
});

it('sandbox env', function () {
    $plugin = new CancelCurrencyPayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'env' => 1,
        'order_id' => 'ORDER_001',
        'refund_reason' => 'refund',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toEqual(1);
});

it('default values', function () {
    $plugin = new CancelCurrencyPayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'order_id' => 'ORDER_001',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toBeNull();
    expect($payload->get('refund_reason'))->toBeNull();
});

