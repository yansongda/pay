<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\RefundOrderPlugin;
use Yansongda\Supports\Collection;

it('携带订单号', function () {
    $plugin = new RefundOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'order_id' => '123456',
        'refund_order_id' => 'REFUND_001',
        'left_fee' => 100,
        'refund_fee' => 50,
        'biz_meta' => 'test_meta',
        'refund_reason' => '1',
        'req_from' => '2',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('/xpay/refund_order');
    expect($result->getPayload()->get('openid'))->toEqual('test_openid');
    expect($result->getPayload()->get('order_id'))->toEqual('123456');
    expect($result->getPayload()->get('wx_order_id'))->toBeNull();
    expect($result->getPayload()->get('refund_order_id'))->toEqual('REFUND_001');
    expect($result->getPayload()->get('left_fee'))->toEqual(100);
    expect($result->getPayload()->get('refund_fee'))->toEqual(50);
    expect($result->getPayload()->get('biz_meta'))->toEqual('test_meta');
    expect($result->getPayload()->get('refund_reason'))->toEqual('1');
    expect($result->getPayload()->get('req_from'))->toEqual('2');
});

it('携带微信订单号', function () {
    $plugin = new RefundOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'wx_order_id' => 'wx_123456',
        'refund_order_id' => 'REFUND_001',
        'left_fee' => 100,
        'refund_fee' => 50,
        'refund_reason' => '1',
        'req_from' => '2',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('order_id'))->toBeNull();
    expect($result->getPayload()->get('wx_order_id'))->toEqual('wx_123456');
});

it('携带 access token', function () {
    $plugin = new RefundOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'test_openid',
        'order_id' => '123456',
        'refund_order_id' => 'REFUND_001',
        'left_fee' => 100,
        'refund_fee' => 50,
        'refund_reason' => '1',
        'req_from' => '2',
        'access_token' => 'test_token',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('access_token'))->toEqual('test_token');
});

