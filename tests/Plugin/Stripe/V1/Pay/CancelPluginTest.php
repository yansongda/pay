<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\CancelPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new CancelPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['payment_intent_id' => 'pi_test123'])->setPayload(new Collection([]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toContain('pi_test123');
    expect($payload->get('_url'))->toContain('cancel');
});

it('URL 包含撤销', function () {
    $plugin = new CancelPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['payment_intent_id' => 'pi_abc'])->setPayload(new Collection([]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_url'))->toEqual('/v1/payment_intents/pi_abc/cancel');
});

it('缺少 payment_intent_id', function () {
    $plugin = new CancelPlugin();

    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection([]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

