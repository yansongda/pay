<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Withdraw\CreateWithdrawOrderPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new CreateWithdrawOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'withdraw_no' => 'WITHDRAW_001',
        'withdraw_amount' => '0.01',
        'env' => 0,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/create_withdraw_order');
    expect($payload->get('withdraw_no'))->toEqual('WITHDRAW_001');
    expect($payload->get('withdraw_amount'))->toEqual('0.01');
});

it('不携带提现金额', function () {
    $plugin = new CreateWithdrawOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'withdraw_no' => 'WITHDRAW_001',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('withdraw_no'))->toEqual('WITHDRAW_001');
    expect($payload->get('withdraw_amount'))->toBeNull();
});

it('沙箱环境', function () {
    $plugin = new CreateWithdrawOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'withdraw_no' => 'WITHDRAW_001',
        'withdraw_amount' => '0.01',
        'env' => 1,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('withdraw_no'))->toEqual('WITHDRAW_001');
});

