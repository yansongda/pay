<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\ModifyPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new ModifyPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 修改支付分订单金额，参数缺少 `out_order_no`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少商户订单号', function () {
    $plugin = new ModifyPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['total_amount' => 200, 'reason' => '测试修改金额']));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 修改支付分订单金额，参数缺少 `out_order_no`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少服务商 ID', function () {
    $plugin = new ModifyPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
        'out_order_no' => '202609100003',
        'total_amount' => 200,
        'reason' => '测试修改金额',
    ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 修改支付分订单金额，参数缺少 `service_id`', Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);

it('正常', function () {
    $plugin = new ModifyPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202609100003',
        'total_amount' => 200,
        'reason' => '测试修改金额',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        'total_amount' => 200,
        'reason' => '测试修改金额',
        '_method' => 'POST',
        '_url' => '/v3/payscore/serviceorder/202609100003/modify',
        'appid' => 'wx55955316af4ef13',
        'service_id' => '12345678901234567890123456789012',
    ]);
    expect($result->getPayload()->has('out_order_no'))->toBeFalse();
    expect($result->getPayload()->has('mchid'))->toBeFalse();
});

it('携带 payload app_id 与服务商 ID', function () {
    $plugin = new ModifyPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202609100003',
        'appid' => 'payload_app_id',
        'service_id' => 'payload_service_id',
        'total_amount' => 200,
        'reason' => '测试修改金额',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        'appid' => 'payload_app_id',
        'service_id' => 'payload_service_id',
        'total_amount' => 200,
        'reason' => '测试修改金额',
        '_method' => 'POST',
        '_url' => '/v3/payscore/serviceorder/202609100003/modify',
    ]);
    expect($result->getPayload()->has('out_order_no'))->toBeFalse();
    expect($result->getPayload()->has('mchid'))->toBeFalse();
});

