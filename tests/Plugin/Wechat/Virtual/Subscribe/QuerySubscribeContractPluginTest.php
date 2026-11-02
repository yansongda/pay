<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Subscribe\QuerySubscribeContractPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new QuerySubscribeContractPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/query_subscribe_contract');
    expect($payload->get('openid'))->toEqual('test_openid');
    expect($payload->get('env'))->toBeNull();
});

it('沙箱环境', function () {
    $plugin = new QuerySubscribeContractPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'env' => 1,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toEqual(1);
    expect($payload->get('_url'))->toEqual('/xpay/query_subscribe_contract');
});

it('默认环境', function () {
    $plugin = new QuerySubscribeContractPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toBeNull();
});

