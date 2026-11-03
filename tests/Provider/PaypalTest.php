<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

describe('shortcut', function () {
    it('不存在', function () {

        Pay::paypal()->foo();
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_INVALID);

    it('不兼容', function () {

        Pay::paypal()->foo();
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_INVALID);
});

describe('action', function () {
    it('撤销', function () {

        Pay::paypal()->cancel([]);
    })->throws(InvalidParamsException::class, null, \Yansongda\Pay\Exception\Exception::PARAMS_METHOD_NOT_SUPPORTED);

    it('关单', function () {

        Pay::paypal()->close([]);
    })->throws(InvalidParamsException::class, null, \Yansongda\Pay\Exception\Exception::PARAMS_METHOD_NOT_SUPPORTED);
});

describe('callback', function () {
    it('正常', function () {
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

    it('数组形式', function () {
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
});

describe('success', function () {
    it('正常', function () {
        $result = Pay::paypal()->success();

        expect($result)->toBeInstanceOf(ResponseInterface::class);
        expect((string) $result->getBody())->toContain('success');
    });
});