<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\SyncPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new SyncPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 同步支付分订单，参数缺少 `out_order_no`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少商户订单号', function () {
    $plugin = new SyncPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['type' => 'Order_Paid']));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 同步支付分订单，参数缺少 `out_order_no`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少服务商 ID', function () {
    $plugin = new SyncPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
        'out_order_no' => '202609100004',
        'type' => 'Order_Paid',
        'detail' => ['paid_time' => '20260910000140'],
    ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 同步支付分订单，参数缺少 `service_id`', Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);

it('正常', function () {
    $plugin = new SyncPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202609100004',
        'type' => 'Order_Paid',
        'detail' => ['paid_time' => '20260910000140'],
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        'type' => 'Order_Paid',
        'detail' => ['paid_time' => '20260910000140'],
        '_method' => 'POST',
        '_url' => '/v3/payscore/serviceorder/202609100004/sync',
        'appid' => 'wx55955316af4ef13',
        'service_id' => '12345678901234567890123456789012',
    ]);
    expect($result->getPayload()->has('out_order_no'))->toBeFalse();
    expect($result->getPayload()->has('mchid'))->toBeFalse();
});

it('携带 payload app_id 与服务商 ID', function () {
    $plugin = new SyncPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202609100004',
        'appid' => 'payload_app_id',
        'service_id' => 'payload_service_id',
        'type' => 'Order_Paid',
        'detail' => ['paid_time' => '20260910000140'],
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        'appid' => 'payload_app_id',
        'service_id' => 'payload_service_id',
        'type' => 'Order_Paid',
        'detail' => ['paid_time' => '20260910000140'],
        '_method' => 'POST',
        '_url' => '/v3/payscore/serviceorder/202609100004/sync',
    ]);
    expect($result->getPayload()->has('out_order_no'))->toBeFalse();
    expect($result->getPayload()->has('mchid'))->toBeFalse();
});

