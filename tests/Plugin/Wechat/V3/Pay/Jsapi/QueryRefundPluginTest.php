<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\QueryRefundPlugin;
use Yansongda\Supports\Collection;

it('empty payload', function () {
    $plugin = new QueryRefundPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: Jsapi 查询退款订单，参数缺少 `out_refund_no`');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal', function () {
    $plugin = new QueryRefundPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_refund_no' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('GET');
    expect($result->getPayload()->get('_url'))->toEqual('/v3/refund/domestic/refunds/111');
});

it('service params', function () {
    $plugin = new QueryRefundPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_refund_no' => '111',
        'sub_mchid' => '333',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('GET');
    expect($result->getPayload()->get('_service_url'))->toEqual('/v3/refund/domestic/refunds/111?sub_mchid=333');
});

it('service', function () {
    $plugin = new QueryRefundPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'out_refund_no' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('GET');
    expect($result->getPayload()->get('_service_url'))->toEqual('/v3/refund/domestic/refunds/111?sub_mchid=1600314070');
});

