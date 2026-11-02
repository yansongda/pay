<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\QueryPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: Jsapi 通过商户订单号查询订单，参数缺少 `out_trade_no`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_trade_no' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('GET');
    expect($result->getPayload()->get('_url'))->toEqual('/v3/pay/transactions/out-trade-no/111?mchid=1600314069');
});

it('服务商参数', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_trade_no' => '111',
        'sub_mchid' => '333',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('GET');
    expect($result->getPayload()->get('_service_url'))->toEqual('/v3/pay/partner/transactions/out-trade-no/111?sp_mchid=1600314069&sub_mchid=333');
});

it('服务商', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'out_trade_no' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('GET');
    expect($result->getPayload()->get('_service_url'))->toEqual('/v3/pay/partner/transactions/out-trade-no/111?sp_mchid=1600314069&sub_mchid=1600314070');
});

