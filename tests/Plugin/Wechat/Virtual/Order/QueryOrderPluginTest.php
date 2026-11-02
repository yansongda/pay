<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\QueryOrderPlugin;
use Yansongda\Supports\Collection;

it('with order id', function () {
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

it('with wx order id', function () {
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

it('with both order ids', function () {
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

it('env as string', function () {
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

it('with access token', function () {
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

