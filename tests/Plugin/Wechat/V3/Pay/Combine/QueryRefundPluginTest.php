<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\QueryRefundPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new QueryRefundPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 合单查询退款订单，参数缺少 `out_refund_no`');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('正常', function () {
    $plugin = new QueryRefundPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_refund_no' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('GET');
    expect($result->getPayload()->get('_url'))->toEqual('/v3/refund/domestic/refunds/111');
});

it('服务商参数', function () {
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

it('服务商', function () {
    $plugin = new QueryRefundPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'out_refund_no' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('GET');
    expect($result->getPayload()->get('_service_url'))->toEqual('/v3/refund/domestic/refunds/111?sub_mchid=1600314070');
});

