<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

it('快捷方式不存在', function () {

    Pay::stripe()->foo();
})->throws(InvalidParamsException::class, null, \Yansongda\Artful\Exception\Exception::PARAMS_SHORTCUT_INVALID);

it('关单', function () {

    Pay::stripe()->close([]);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_METHOD_NOT_SUPPORTED);

it('回调', function () {
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

    $result = Pay::stripe()->callback($request);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->get('id'))->toEqual('evt_test123');
    expect($result->get('type'))->toEqual('payment_intent.succeeded');
});

it('数组形式回调', function () {
    $body = json_encode([
        'id' => 'evt_test456',
        'type' => 'charge.succeeded',
        'data' => [
            'object' => [
                'id' => 'ch_test456',
                'status' => 'succeeded',
            ],
        ],
    ]);

    $timestamp = time();
    $webhookSecret = 'whsec_stripe_webhook_secret';
    $expectedSig = hash_hmac('sha256', $timestamp.'.'.$body, $webhookSecret);
    $signatureHeader = 't='.$timestamp.',v1='.$expectedSig;

    $result = Pay::stripe()->callback([
        'headers' => ['Stripe-Signature' => $signatureHeader],
        'body' => $body,
    ]);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->get('id'))->toEqual('evt_test456');
    expect($result->get('type'))->toEqual('charge.succeeded');
});

it('成功', function () {
    $result = Pay::stripe()->success();

    expect($result)->toBeInstanceOf(ResponseInterface::class);
    expect((string) $result->getBody())->toContain('success');
});

