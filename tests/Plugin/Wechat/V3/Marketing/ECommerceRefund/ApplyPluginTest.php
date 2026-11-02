<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\ECommerceRefund\ApplyPlugin;
use Yansongda\Supports\Collection;

it('模式错误', function () {
    $plugin = new ApplyPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 平台收付通（退款）-申请退款，只支持服务商模式，当前配置为普通商户模式', Exception::PARAMS_PLUGIN_ONLY_SUPPORT_SERVICE_MODE);

it('空 payload', function () {
    $plugin = new ApplyPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider']);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 平台收付通（退款）-申请退款，缺少必要参数', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('普通参数', function () {
    $plugin = new ApplyPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'test' => 'yansongda',
        'sub_mchid' => '1111',
        'sp_appid' => '2222',
        'notify_url' => '3333',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_service_url' => '/v3/ecommerce/refunds/apply',
        'sub_mchid' => '1111',
        'sp_appid' => '2222',
        'notify_url' => '3333',
        'test' => 'yansongda',
    ]);
});

it('正常', function () {
    $plugin = new ApplyPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'test' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_service_url' => '/v3/ecommerce/refunds/apply',
        'sub_mchid' => '1600314070',
        'sp_appid' => 'wx55955316af4ef13',
        'notify_url' => 'https://pay.yansongda.cn',
        'test' => 'yansongda',
    ]);
});

