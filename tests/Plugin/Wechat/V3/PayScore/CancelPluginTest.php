<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\CancelPlugin;
use Yansongda\Supports\Collection;

it('empty payload', function () {
    $plugin = new CancelPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 取消支付分订单，参数缺少 `out_order_no`');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('missing out order no', function () {
    $plugin = new CancelPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection(['reason' => '测试取消']));

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 取消支付分订单，参数缺少 `out_order_no`');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('missing service id', function () {
    $plugin = new CancelPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
        'out_order_no' => '202609100001',
        'reason' => '测试取消',
    ]));

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);
    $this->expectExceptionMessage('参数异常: 取消支付分订单，参数缺少 `service_id`');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal', function () {
    $plugin = new CancelPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202609100001',
        'reason' => '测试取消',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        'reason' => '测试取消',
        '_method' => 'POST',
        '_url' => '/v3/payscore/serviceorder/202609100001/cancel',
        'appid' => 'wx55955316af4ef13',
        'service_id' => '12345678901234567890123456789012',
    ]);
    expect($result->getPayload()->has('out_order_no'))->toBeFalse();
    expect($result->getPayload()->has('mchid'))->toBeFalse();
});

it('normal with payload app id and service id', function () {
    $plugin = new CancelPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202609100001',
        'appid' => 'payload_app_id',
        'service_id' => 'payload_service_id',
        'reason' => '测试取消',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        'appid' => 'payload_app_id',
        'service_id' => 'payload_service_id',
        'reason' => '测试取消',
        '_method' => 'POST',
        '_url' => '/v3/payscore/serviceorder/202609100001/cancel',
    ]);
    expect($result->getPayload()->has('out_order_no'))->toBeFalse();
    expect($result->getPayload()->has('mchid'))->toBeFalse();
});

