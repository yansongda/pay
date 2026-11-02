<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\QueryOrderPlugin;
use Yansongda\Supports\Collection;

it('携带订单号', function () {
    $plugin = new QueryOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'env' => 0,
        'order_id' => '123456',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('/xpay/query_order');
    expect($result->getPayload()->get('openid'))->toEqual('test_openid');
    expect($result->getPayload()->get('order_id'))->toEqual('123456');
    expect($result->getPayload()->get('wx_order_id'))->toBeNull();
});

it('携带微信订单号', function () {
    $plugin = new QueryOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'env' => 0,
        'wx_order_id' => 'wx_123456',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('/xpay/query_order');
    expect($result->getPayload()->get('openid'))->toEqual('test_openid');
    expect($result->getPayload()->get('order_id'))->toBeNull();
    expect($result->getPayload()->get('wx_order_id'))->toEqual('wx_123456');
});

it('同时携带两个订单号', function () {
    $plugin = new QueryOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'env' => 0,
        'order_id' => '123456',
        'wx_order_id' => 'wx_123456',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('order_id'))->toEqual('123456');
    expect($result->getPayload()->get('wx_order_id'))->toEqual('wx_123456');
});

it('字符串形式的环境', function () {
    $plugin = new QueryOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'env' => '0',
        'order_id' => '123456',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('order_id'))->toEqual('123456');
});

it('携带 access token', function () {
    $plugin = new QueryOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'env' => 0,
        'order_id' => '123456',
        'access_token' => 'test_token',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('access_token'))->toEqual('test_token');
});

