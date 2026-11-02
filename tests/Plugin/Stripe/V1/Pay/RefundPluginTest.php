<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\RefundPlugin;
use Yansongda\Supports\Collection;

it('携带 payment intent 的正常场景', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['payment_intent' => 'pi_test123'])->setPayload(new Collection([]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/v1/refunds');
});

it('携带 charge 的正常场景', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['charge' => 'ch_test789'])->setPayload(new Collection([]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/v1/refunds');
});

it('payload 中携带 payment intent', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection(['payment_intent' => 'pi_test123']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/v1/refunds');
});

it('缺少 payment intent 与 charge', function () {
    $plugin = new RefundPlugin();

    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection([]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

