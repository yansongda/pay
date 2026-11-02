<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Subscribe\SendSubscribePrePaymentPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new SendSubscribePrePaymentPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'contract_id' => 'test_contract_id',
        'pre_payment_amount' => 100,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/send_subscribe_pre_payment');
    expect($payload->get('openid'))->toEqual('test_openid');
    expect($payload->get('env'))->toBeNull();
    expect($payload->get('contract_id'))->toEqual('test_contract_id');
    expect($payload->get('pre_payment_amount'))->toEqual(100);
});

it('沙箱环境', function () {
    $plugin = new SendSubscribePrePaymentPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'env' => 1,
        'contract_id' => 'test_contract_id',
        'pre_payment_amount' => 100,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toEqual(1);
});

it('默认值', function () {
    $plugin = new SendSubscribePrePaymentPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'contract_id' => 'test_contract_id',
        'pre_payment_amount' => 100,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toBeNull();
    expect($payload->get('_url'))->toEqual('/xpay/send_subscribe_pre_payment');
});

