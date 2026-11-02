<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\App\QueryRefundPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new QueryRefundPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: App 查询退款订单，参数缺少 `out_refund_no`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

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

