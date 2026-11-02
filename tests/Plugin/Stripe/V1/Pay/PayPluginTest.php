<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\PayPlugin;
use Yansongda\Supports\Collection;

it('normal', function () {
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

it('method and url are set', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection(['amount' => 500, 'currency' => 'eur']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('/v1/payment_intents');
});

it('missing amount throws exception', function () {
    $plugin = new PayPlugin();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection(['currency' => 'usd']));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('missing currency throws exception', function () {
    $plugin = new PayPlugin();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection(['amount' => 1000]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

