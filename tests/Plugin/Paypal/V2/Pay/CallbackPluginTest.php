<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Paypal\V2\Pay\CallbackPlugin;

it('缺少请求对象抛出异常', function () {
    $plugin = new CallbackPlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_CALLBACK_REQUEST_INVALID);

it('正常回调', function () {
    $plugin = new CallbackPlugin();
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

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $request, '_params' => []]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->not->toBeEmpty();
    expect($result->getDestination()->all())->not->toBeEmpty();
    expect($result->getPayload()->get('resource.id'))->toEqual('ORDER_123');
});

it('非法 JSON body 抛出异常', function () {
    $plugin = new CallbackPlugin();
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
    ], 'not-a-valid-json');

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $request, '_params' => []]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_PAYPAL_BODY_INVALID);

