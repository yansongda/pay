<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Stripe\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\ResponsePlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Supports\Collection;

it('shortcut not found', function () {
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(\Yansongda\Artful\Exception\Exception::PARAMS_SHORTCUT_INVALID);

    Pay::stripe()->foo();
});

it('close', function () {
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_METHOD_NOT_SUPPORTED);

    Pay::stripe()->close([]);
});

it('callback', function () {
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

it('callback with array', function () {
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

it('success', function () {
    $result = Pay::stripe()->success();

    expect($result)->toBeInstanceOf(ResponseInterface::class);
    expect((string) $result->getBody())->toContain('success');
});

