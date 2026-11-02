<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\PayPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection([
        'amount' => 1000,
        'currency' => 'usd',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/v1/payment_intents');
    expect($payload->get('amount'))->toEqual(1000);
    expect($payload->get('currency'))->toEqual('usd');
});

it('方法与 URL 已设置', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection(['amount' => 500, 'currency' => 'eur']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('/v1/payment_intents');
});

it('缺少金额抛出异常', function () {
    $plugin = new PayPlugin();

    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection(['currency' => 'usd']));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少币种抛出异常', function () {
    $plugin = new PayPlugin();

    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection(['amount' => 1000]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

