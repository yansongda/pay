<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\RefundPlugin;
use Yansongda\Supports\Collection;

it('normal with payment intent', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['payment_intent' => 'pi_test123'])->setPayload(new Collection([]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/v1/refunds');
});

it('normal with charge', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['charge' => 'ch_test789'])->setPayload(new Collection([]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/v1/refunds');
});

it('normal with payment intent in payload', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection(['payment_intent' => 'pi_test123']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/v1/refunds');
});

it('missing payment intent and charge', function () {
    $plugin = new RefundPlugin();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection([]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

