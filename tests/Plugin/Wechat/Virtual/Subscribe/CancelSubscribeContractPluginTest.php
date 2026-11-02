<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Subscribe\CancelSubscribeContractPlugin;
use Yansongda\Supports\Collection;

it('normal', function () {
    $plugin = new CancelSubscribeContractPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'contract_id' => 'test_contract_id',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/cancel_subscribe_contract');
    expect($payload->get('openid'))->toEqual('test_openid');
    expect($payload->get('env'))->toBeNull();
    expect($payload->get('contract_id'))->toEqual('test_contract_id');
});

it('sandbox env', function () {
    $plugin = new CancelSubscribeContractPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'env' => 1,
        'contract_id' => 'test_contract_id',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toEqual(1);
});

it('default values', function () {
    $plugin = new CancelSubscribeContractPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'contract_id' => 'test_contract_id',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toBeNull();
    expect($payload->get('_url'))->toEqual('/xpay/cancel_subscribe_contract');
});

