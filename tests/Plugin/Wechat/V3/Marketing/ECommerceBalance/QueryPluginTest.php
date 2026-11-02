<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\ECommerceBalance\QueryPlugin;
use Yansongda\Supports\Collection;

it('mode wrong', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_SERVICE_MODE);
    $this->expectExceptionMessage('参数异常: 查询电商平台账户实时余额，只支持服务商模式，当前配置为普通商户模式');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('empty payload', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider']);

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 查询电商平台账户实时余额，参数缺少 `account_type`');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal params', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])
        ->setPayload(new Collection([
            'account_type' => '111',
            'aaa' => '222',
        ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_service_url' => '/v3/merchant/fund/balance/111',
    ]);
});

it('normal', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])
        ->setPayload(new Collection([
            'account_type' => '111',
        ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_service_url' => '/v3/merchant/fund/balance/111',
    ]);
});

