<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\PayPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 支付分订单催收，参数缺少 `out_order_no`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少商户订单号', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['appid' => 'payload_app_id']));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 支付分订单催收，参数缺少 `out_order_no`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少服务商 ID', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
        'out_order_no' => '202609100005',
    ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 支付分订单催收，参数缺少 `service_id`', Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);

it('正常', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202609100005',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/payscore/serviceorder/202609100005/pay',
        'appid' => 'wx55955316af4ef13',
        'service_id' => '12345678901234567890123456789012',
    ]);
    expect($result->getPayload()->has('out_order_no'))->toBeFalse();
    expect($result->getPayload()->has('mchid'))->toBeFalse();
});

it('携带 payload app_id 与服务商 ID', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202609100005',
        'appid' => 'payload_app_id',
        'service_id' => 'payload_service_id',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        'appid' => 'payload_app_id',
        'service_id' => 'payload_service_id',
        '_method' => 'POST',
        '_url' => '/v3/payscore/serviceorder/202609100005/pay',
    ]);
    expect($result->getPayload()->has('out_order_no'))->toBeFalse();
    expect($result->getPayload()->has('mchid'))->toBeFalse();
});

