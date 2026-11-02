<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Subscribe\SubmitSubscribePayOrderPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new SubmitSubscribePayOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'contract_id' => 'test_contract_id',
        'amount' => 100,
        'order_id' => 'test_order_id',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/submit_subscribe_pay_order');
    expect($payload->get('openid'))->toEqual('test_openid');
    expect($payload->get('env'))->toBeNull();
    expect($payload->get('contract_id'))->toEqual('test_contract_id');
    expect($payload->get('amount'))->toEqual(100);
    expect($payload->get('order_id'))->toEqual('test_order_id');
});

it('沙箱环境', function () {
    $plugin = new SubmitSubscribePayOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'env' => 1,
        'contract_id' => 'test_contract_id',
        'amount' => 100,
        'order_id' => 'test_order_id',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toEqual(1);
});

it('默认值', function () {
    $plugin = new SubmitSubscribePayOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'contract_id' => 'test_contract_id',
        'amount' => 100,
        'order_id' => 'test_order_id',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toBeNull();
    expect($payload->get('_url'))->toEqual('/xpay/submit_subscribe_pay_order');
});

