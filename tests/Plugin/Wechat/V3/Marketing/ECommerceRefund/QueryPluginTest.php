<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\ECommerceRefund\QueryPlugin;
use Yansongda\Supports\Collection;

it('模式错误', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 平台收付通（退款）-查询单笔退款（按商户退款单号），只支持服务商模式，当前配置为普通商户模式', Exception::PARAMS_PLUGIN_ONLY_SUPPORT_SERVICE_MODE);

it('空 payload', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider']);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 平台收付通（退款）-查询单笔退款（按商户退款单号），缺少必要参数 `out_refund_no`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('普通参数', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])
        ->setPayload(new Collection([
            'out_refund_no' => '111',
            'sub_mchid' => '222',
        ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_service_url' => '/v3/ecommerce/refunds/out-refund-no/111?sub_mchid=222',
    ]);
});

it('正常', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])
        ->setPayload(new Collection([
            'out_refund_no' => '111',
        ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_service_url' => '/v3/ecommerce/refunds/out-refund-no/111?sub_mchid=1600314070',
    ]);
});

