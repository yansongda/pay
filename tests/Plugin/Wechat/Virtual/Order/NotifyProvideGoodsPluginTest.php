<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\NotifyProvideGoodsPlugin;
use Yansongda\Supports\Collection;

it('携带订单号', function () {
    $plugin = new NotifyProvideGoodsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'order_id' => '1234567890',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/notify_provide_goods');
    expect($payload->get('order_id'))->toEqual('1234567890');
    expect($payload->get('wx_order_id'))->toBeNull();
});

it('携带微信订单号', function () {
    $plugin = new NotifyProvideGoodsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'wx_order_id' => 'wx_123456',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('order_id'))->toBeNull();
    expect($payload->get('wx_order_id'))->toEqual('wx_123456');
});

it('同时携带两个订单号', function () {
    $plugin = new NotifyProvideGoodsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'order_id' => '1234567890',
        'wx_order_id' => 'wx_123456',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('order_id'))->toEqual('1234567890');
    expect($payload->get('wx_order_id'))->toEqual('wx_123456');
});

it('携带 access token', function () {
    $plugin = new NotifyProvideGoodsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'order_id' => '1234567890',
        'access_token' => 'test_access_token',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('access_token'))->toEqual('test_access_token');
});

