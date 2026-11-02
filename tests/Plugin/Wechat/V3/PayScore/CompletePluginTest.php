<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\CompletePlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new CompletePlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 完结支付分订单，参数缺少 `out_order_no`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少商户订单号', function () {
    $plugin = new CompletePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['total_amount' => 100]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 完结支付分订单，参数缺少 `out_order_no`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少服务商 ID', function () {
    $plugin = new CompletePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
        'out_order_no' => '202609100002',
        'total_amount' => 100,
    ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 完结支付分订单，参数缺少 `service_id`', Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);

it('正常', function () {
    $plugin = new CompletePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202609100002',
        'post_payments' => [['name' => '测试商品', 'amount' => 100]],
        'total_amount' => 100,
        'time_range' => ['start_time' => '20260910000140'],
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        'post_payments' => [['name' => '测试商品', 'amount' => 100]],
        'total_amount' => 100,
        'time_range' => ['start_time' => '20260910000140'],
        '_method' => 'POST',
        '_url' => '/v3/payscore/serviceorder/202609100002/complete',
        'appid' => 'wx55955316af4ef13',
        'service_id' => '12345678901234567890123456789012',
    ]);
    expect($result->getPayload()->has('out_order_no'))->toBeFalse();
    expect($result->getPayload()->has('mchid'))->toBeFalse();
});

it('携带 payload app_id 与服务商 ID', function () {
    $plugin = new CompletePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202609100002',
        'appid' => 'payload_app_id',
        'service_id' => 'payload_service_id',
        'total_amount' => 100,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        'appid' => 'payload_app_id',
        'service_id' => 'payload_service_id',
        'total_amount' => 100,
        '_method' => 'POST',
        '_url' => '/v3/payscore/serviceorder/202609100002/complete',
    ]);
    expect($result->getPayload()->has('out_order_no'))->toBeFalse();
    expect($result->getPayload()->has('mchid'))->toBeFalse();
});

