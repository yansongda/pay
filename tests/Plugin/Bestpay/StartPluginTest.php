<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Bestpay\V1\StartPlugin;

it('组装', function () {
    $rocket = new Rocket();
    $rocket->setParams(['outTradeNo' => 'ORDER001', '_config' => 'default']);

    $rocket = (new StartPlugin())->assembly($rocket, fn ($r) => $r);

    $payload = $rocket->getPayload();

    expect($payload->get('outTradeNo'))->toEqual('ORDER001');
    expect($payload->get('institutionType'))->toEqual('MERCHANT');
    expect($payload->get('institutionCode'))->toEqual('3178033925245778');
});

it('组装合并配置中的商户号', function () {
    $rocket = new Rocket();
    $rocket->setParams(['outTradeNo' => 'ORDER001']);

    $rocket = (new StartPlugin())->assembly($rocket, fn ($r) => $r);

    expect($rocket->getPayload()->get('merchantNo'))->toEqual('3178033925245778');
});

it('用户参数覆盖配置组装', function () {
    $rocket = new Rocket();
    $rocket->setParams([
        'merchantNo' => 'CUSTOM_MCH',
        'institutionCode' => 'CUSTOM_INST',
    ]);

    $rocket = (new StartPlugin())->assembly($rocket, fn ($r) => $r);

    $payload = $rocket->getPayload();

    expect($payload->get('merchantNo'))->toEqual('CUSTOM_MCH');
    expect($payload->get('institutionCode'))->toEqual('CUSTOM_INST');
});

