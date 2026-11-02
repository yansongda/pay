<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Withdraw\QueryWithdrawOrderPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new QueryWithdrawOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'withdraw_no' => 'WITHDRAW_001',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/query_withdraw_order');
    expect($payload->get('withdraw_no'))->toEqual('WITHDRAW_001');
});

it('携带 access token', function () {
    $plugin = new QueryWithdrawOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'withdraw_no' => 'WITHDRAW_001',
        'access_token' => 'test_token',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('access_token'))->toEqual('test_token');
});

