<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\CallbackPlugin;

it('缺少请求对象抛出异常', function () {
    $plugin = new CallbackPlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_CALLBACK_REQUEST_INVALID);

it('非法 JSON body 抛出异常', function () {
    $plugin = new CallbackPlugin();
    $body = 'not-valid-json';
    $timestamp = time();
    $webhookSecret = 'whsec_stripe_webhook_secret';
    $expectedSig = hash_hmac('sha256', $timestamp.'.'.$body, $webhookSecret);
    $signatureHeader = 't='.$timestamp.',v1='.$expectedSig;

    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/stripe/notify', [
        'Stripe-Signature' => $signatureHeader,
    ], $body);

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $request, '_params' => []]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_STRIPE_BODY_INVALID);

it('正常回调', function () {
    $plugin = new CallbackPlugin();
    $body = json_encode([
        'id' => 'evt_test123',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_test123',
                'status' => 'succeeded',
            ],
        ],
    ]);

    $timestamp = time();
    $webhookSecret = 'whsec_stripe_webhook_secret';
    $expectedSig = hash_hmac('sha256', $timestamp.'.'.$body, $webhookSecret);
    $signatureHeader = 't='.$timestamp.',v1='.$expectedSig;

    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/stripe/notify', [
        'Stripe-Signature' => $signatureHeader,
    ], $body);

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $request, '_params' => []]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->not->toBeEmpty();
    expect($result->getDestination()->all())->not->toBeEmpty();
    expect($result->getPayload()->get('id'))->toEqual('evt_test123');
    expect($result->getPayload()->get('type'))->toEqual('payment_intent.succeeded');
});

