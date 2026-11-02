<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\ECommerceRefund\QueryReturnAdvancePlugin;
use Yansongda\Supports\Collection;

it('mode wrong', function () {
    $plugin = new QueryReturnAdvancePlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_SERVICE_MODE);
    $this->expectExceptionMessage('参数异常: 平台收付通（退款）-查询垫付回补结果，只支持服务商模式，当前配置为普通商户模式');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('empty payload', function () {
    $plugin = new QueryReturnAdvancePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider']);

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 平台收付通（退款）-查询垫付回补结果，缺少必要参数 `refund_id`');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal params', function () {
    $plugin = new QueryReturnAdvancePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])
        ->setPayload(new Collection([
            'refund_id' => '111',
            'sub_mchid' => '222',
        ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_service_url' => '/v3/ecommerce/refunds/111/return-advance?sub_mchid=222',
    ]);
});

it('normal', function () {
    $plugin = new QueryReturnAdvancePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])
        ->setPayload(new Collection([
            'refund_id' => '111',
        ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_service_url' => '/v3/ecommerce/refunds/111/return-advance?sub_mchid=1600314070',
    ]);
});

