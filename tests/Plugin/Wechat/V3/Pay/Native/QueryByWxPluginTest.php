<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\QueryByWxPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new QueryByWxPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: Native 通过微信订单号查询订单，参数缺少 `transaction_id`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new QueryByWxPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'transaction_id' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('GET');
    expect($result->getPayload()->get('_url'))->toEqual('/v3/pay/transactions/id/111?mchid=1600314069');
});

it('服务商参数', function () {
    $plugin = new QueryByWxPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'transaction_id' => '111',
        'sub_mchid' => '333',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('GET');
    expect($result->getPayload()->get('_service_url'))->toEqual('/v3/pay/partner/transactions/id/111?sp_mchid=1600314069&sub_mchid=333');
});

it('服务商', function () {
    $plugin = new QueryByWxPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'transaction_id' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('GET');
    expect($result->getPayload()->get('_service_url'))->toEqual('/v3/pay/partner/transactions/id/111?sp_mchid=1600314069&sub_mchid=1600314070');
});

