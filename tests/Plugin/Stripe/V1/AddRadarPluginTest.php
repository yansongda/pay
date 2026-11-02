<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Stripe\V1\AddRadarPlugin;
use Yansongda\Supports\Collection;

it('POST 请求', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => '/v1/payment_intents',
        'amount' => 1000,
        'currency' => 'usd',
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $radar = $result->getRadar();

    expect($radar->getHeaderLine('User-Agent'))->toEqual('yansongda/pay-v3');
    expect($radar->getHeaderLine('Content-Type'))->toEqual('application/x-www-form-urlencoded');
    expect($radar->getHeaderLine('Authorization'))->toEqual('Bearer sk_test_stripe_secret');
    expect($radar->getMethod())->toEqual('POST');
    expect((string) $radar->getUri())->toContain('v1/payment_intents');
});

it('获取请求', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'GET',
        '_url' => '/v1/payment_intents/pi_test123',
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $radar = $result->getRadar();

    expect($radar->getMethod())->toEqual('GET');
    expect($radar->getHeaderLine('Authorization'))->toEqual('Bearer sk_test_stripe_secret');
    expect($radar->getHeaderLine('Content-Type'))->toBeEmpty();
    expect((string) $radar->getBody())->toEqual('');
});

it('获取带查询参数的请求', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'GET',
        '_url' => '/v1/payment_intents/pi_test123',
        'expand' => ['charges'],
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $radar = $result->getRadar();

    expect($radar->getMethod())->toEqual('GET');
    expect((string) $radar->getUri())->toContain('expand');
});

it('POST body 为表单编码', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => '/v1/refunds',
        'payment_intent' => 'pi_test123',
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $radar = $result->getRadar();

    $body = (string) $radar->getBody();
    expect($body)->toContain('payment_intent=pi_test123');
});

it('自定义请求头', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => '/v1/payment_intents',
        'amount' => 1000,
        'currency' => 'usd',
        '_headers' => [
            'Idempotency-Key' => 'idempotency_key_test',
            'Stripe-Version' => '2024-06-20',
        ],
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $radar = $result->getRadar();

    expect($radar->getHeaderLine('Idempotency-Key'))->toEqual('idempotency_key_test');
    expect($radar->getHeaderLine('Stripe-Version'))->toEqual('2024-06-20');
    // 默认头不被覆盖
    expect($radar->getHeaderLine('Authorization'))->toEqual('Bearer sk_test_stripe_secret');
    // `_headers` 属于内部参数，不能残留在请求 body/query 中
    expect((string) $radar->getBody())->not->toContain('_headers');
    expect((string) $radar->getUri())->not->toContain('_headers');
});

it('非数组自定义请求头被忽略', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => '/v1/payment_intents',
        'amount' => 1000,
        'currency' => 'usd',
        '_headers' => 'invalid',
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $radar = $result->getRadar();

    expect($radar->getHeaderLine('Authorization'))->toEqual('Bearer sk_test_stripe_secret');
    expect($radar->hasHeader('Idempotency-Key'))->toBeFalse();
});

