<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Withdraw\QueryBizBalancePlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new QueryBizBalancePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'env' => 0,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/query_biz_balance');
    expect($payload->get('env'))->toEqual(0);
});

it('默认环境', function () {
    $plugin = new QueryBizBalancePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toBeNull();
});

it('沙箱环境', function () {
    $plugin = new QueryBizBalancePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'env' => 1,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toEqual(1);
});

