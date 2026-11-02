<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\ECommerceRefund\ApplyPlugin;
use Yansongda\Supports\Collection;

it('mode wrong', function () {
    $plugin = new ApplyPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_SERVICE_MODE);
    $this->expectExceptionMessage('参数异常: 平台收付通（退款）-申请退款，只支持服务商模式，当前配置为普通商户模式');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('empty payload', function () {
    $plugin = new ApplyPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider']);

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 平台收付通（退款）-申请退款，缺少必要参数');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal params', function () {
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

it('normal', function () {
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

