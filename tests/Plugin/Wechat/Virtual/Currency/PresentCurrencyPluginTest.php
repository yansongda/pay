<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Currency\PresentCurrencyPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new PresentCurrencyPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'env' => 0,
        'present_amount' => 50,
        'order_id' => 'GIFT_001',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/present_currency');
    expect($payload->get('openid'))->toEqual('test_openid');
    expect($payload->get('env'))->toEqual(0);
    expect($payload->get('present_amount'))->toEqual(50);
    expect($payload->get('order_id'))->toEqual('GIFT_001');
});

it('沙箱环境', function () {
    $plugin = new PresentCurrencyPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'env' => 1,
        'present_amount' => 50,
        'order_id' => 'GIFT_001',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toEqual(1);
});

it('默认值', function () {
    $plugin = new PresentCurrencyPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'present_amount' => 50,
        'order_id' => 'GIFT_001',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toBeNull();
});

