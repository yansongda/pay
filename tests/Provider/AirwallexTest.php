<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Airwallex\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ObtainAccessTokenPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ResponsePlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Supports\Collection;

it('shortcut not found', function () {
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(\Yansongda\Artful\Exception\Exception::PARAMS_SHORTCUT_INVALID);

    Pay::airwallex()->foo();
});

it('close', function () {
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_METHOD_NOT_SUPPORTED);

    Pay::airwallex()->close([]);
});

it('callback', function () {
    $body = json_encode([
        'id' => 'evt_test123',
        'name' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'int_test123',
                'status' => 'SUCCEEDED',
            ],
        ],
    ]);

    $timestamp = (string) round(microtime(true) * 1000);
    $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/airwallex/notify', [
        'x-timestamp' => $timestamp,
        'x-signature' => $signature,
    ], $body);

    $result = Pay::airwallex()->callback($request);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->get('id'))->toEqual('evt_test123');
    expect($result->get('name'))->toEqual('payment_intent.succeeded');
});

it('callback with array', function () {
    $body = json_encode([
        'id' => 'evt_test456',
        'name' => 'payment_intent.failed',
    ]);

    $timestamp = (string) round(microtime(true) * 1000);
    $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');

    $result = Pay::airwallex()->callback([
        'headers' => [
            'x-timestamp' => $timestamp,
            'x-signature' => $signature,
        ],
        'body' => $body,
    ]);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->get('id'))->toEqual('evt_test456');
    expect($result->get('name'))->toEqual('payment_intent.failed');
});

it('get callback params with plain array payload', function () {
    $provider = Pay::airwallex();
    $method = new \ReflectionMethod($provider, 'getCallbackParams');

    $request = $method->invoke($provider, [
        'id' => 'evt_test789',
        'name' => 'payment_intent.created',
    ]);

    expect($request)->toBeInstanceOf(ServerRequest::class);
    expect((string) $request->getBody())->toContain('payment_intent.created');
});

it('get callback params from globals', function () {
    $provider = Pay::airwallex();
    $method = new \ReflectionMethod($provider, 'getCallbackParams');

    $request = $method->invoke($provider);

    expect($request)->toBeInstanceOf(ServerRequestInterface::class);
});

it('query', function () {
    $tokenResponse = new Response(201, [], json_encode([
        'token' => 'airwallex_query_token',
        'expires_at' => gmdate('Y-m-d\\TH:i:sO', time() + 1800),
    ]));
    $queryResponse = new Response(200, [], json_encode([
        'id' => 'int_query_123',
        'status' => 'SUCCEEDED',
    ]));

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($tokenResponse, $queryResponse);
    Pay::set(HttpClientInterface::class, $http);

    $result = Pay::airwallex()->query(['id' => 'int_query_123']);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->get('id'))->toEqual('int_query_123');
});

it('cancel', function () {
    $tokenResponse = new Response(201, [], json_encode([
        'token' => 'airwallex_cancel_token',
        'expires_at' => gmdate('Y-m-d\\TH:i:sO', time() + 1800),
    ]));
    $cancelResponse = new Response(200, [], json_encode([
        'id' => 'int_cancel_123',
        'status' => 'CANCELLED',
    ]));

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($tokenResponse, $cancelResponse);
    Pay::set(HttpClientInterface::class, $http);

    $result = Pay::airwallex()->cancel(['id' => 'int_cancel_123']);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->get('status'))->toEqual('CANCELLED');
});

it('refund', function () {
    $tokenResponse = new Response(201, [], json_encode([
        'token' => 'airwallex_refund_token',
        'expires_at' => gmdate('Y-m-d\\TH:i:sO', time() + 1800),
    ]));
    $refundResponse = new Response(201, [], json_encode([
        'id' => 'ref_123',
        'status' => 'RECEIVED',
    ]));

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($tokenResponse, $refundResponse);
    Pay::set(HttpClientInterface::class, $http);

    $result = Pay::airwallex()->refund([
        'id' => 'int_refund_123',
        'amount' => 100,
    ]);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->get('id'))->toEqual('ref_123');
});

it('success', function () {
    $result = Pay::airwallex()->success();

    expect($result)->toBeInstanceOf(ResponseInterface::class);
    expect((string) $result->getBody())->toContain('success');
});

