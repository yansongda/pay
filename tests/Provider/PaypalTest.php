<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Paypal\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\ObtainAccessTokenPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\ResponsePlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Supports\Collection;

it('shortcut not found', function () {
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_SHORTCUT_INVALID);

    Pay::paypal()->foo();
});

it('shortcut incompatible', function () {
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_SHORTCUT_INVALID);

    Pay::paypal()->foo();
});

it('cancel', function () {
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(\Yansongda\Pay\Exception\Exception::PARAMS_METHOD_NOT_SUPPORTED);

    Pay::paypal()->cancel([]);
});

it('close', function () {
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(\Yansongda\Pay\Exception\Exception::PARAMS_METHOD_NOT_SUPPORTED);

    Pay::paypal()->close([]);
});

it('callback', function () {
    $body = json_encode([
        'event_type' => 'CHECKOUT.ORDER.APPROVED',
        'resource' => ['id' => 'ORDER_123', 'status' => 'APPROVED'],
    ]);

    $tokenResponse = new Response(200, [], json_encode([
        'access_token' => 'test_token_123',
        'token_type' => 'Bearer',
        'expires_in' => 32400,
    ]));
    $verifyResponse = new Response(200, [], json_encode([
        'verification_status' => 'SUCCESS',
    ]));

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($tokenResponse, $verifyResponse);
    Pay::set(HttpClientInterface::class, $http);

    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/paypal/notify', [
        'PAYPAL-TRANSMISSION-ID' => 'test-id',
        'PAYPAL-TRANSMISSION-TIME' => '2024-01-01T00:00:00Z',
        'PAYPAL-TRANSMISSION-SIG' => 'test-sig',
        'PAYPAL-CERT-URL' => 'https://api.sandbox.paypal.com/v1/notifications/certs/CERT-123',
        'PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
    ], $body);

    $result = Pay::paypal()->callback($request);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->get('event_type'))->toEqual('CHECKOUT.ORDER.APPROVED');
    expect($result->get('resource.id'))->toEqual('ORDER_123');
});

it('callback with array', function () {
    $body = json_encode([
        'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
        'resource' => ['id' => 'CAPTURE_456', 'status' => 'COMPLETED'],
    ]);

    $tokenResponse = new Response(200, [], json_encode([
        'access_token' => 'test_token_456',
        'token_type' => 'Bearer',
        'expires_in' => 32400,
    ]));
    $verifyResponse = new Response(200, [], json_encode([
        'verification_status' => 'SUCCESS',
    ]));

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($tokenResponse, $verifyResponse);
    Pay::set(HttpClientInterface::class, $http);

    $result = Pay::paypal()->callback([
        'headers' => [
            'PAYPAL-TRANSMISSION-ID' => 'test-id',
            'PAYPAL-TRANSMISSION-TIME' => '2024-01-01T00:00:00Z',
            'PAYPAL-TRANSMISSION-SIG' => 'test-sig',
            'PAYPAL-CERT-URL' => 'https://api.sandbox.paypal.com/v1/notifications/certs/CERT-123',
            'PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
        ],
        'body' => $body,
    ]);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->get('event_type'))->toEqual('PAYMENT.CAPTURE.COMPLETED');
    expect($result->get('resource.id'))->toEqual('CAPTURE_456');
});

it('success', function () {
    $result = Pay::paypal()->success();

    expect($result)->toBeInstanceOf(ResponseInterface::class);
    expect((string) $result->getBody())->toContain('success');
});

