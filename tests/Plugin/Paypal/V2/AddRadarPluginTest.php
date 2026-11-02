<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Paypal\V2\AddRadarPlugin;
use Yansongda\Supports\Collection;

it('Bearer 认证', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => '/v2/checkout/orders',
        '_body' => '{"intent":"CAPTURE"}',
        '_access_token' => 'test_token_abc',
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $radar = $result->getRadar();

    expect($radar->getHeaderLine('User-Agent'))->toEqual('yansongda/pay-v3');
    expect($radar->getHeaderLine('Content-Type'))->toEqual('application/json; charset=utf-8');
    expect($radar->getHeaderLine('Authorization'))->toStartWith('Bearer ');
    expect($radar->getHeaderLine('Authorization'))->toContain('test_token_abc');
    expect($radar->getMethod())->toEqual('POST');
    expect((string) $radar->getUri())->toContain('v2/checkout/orders');
});

it('Basic 认证', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => '/v1/oauth2/token',
        '_auth_type' => 'basic',
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $radar = $result->getRadar();

    expect($radar->getHeaderLine('Authorization'))->toStartWith('Basic ');
    expect($radar->getHeaderLine('Content-Type'))->toEqual('application/x-www-form-urlencoded');
    expect((string) $radar->getBody())->toEqual('grant_type=client_credentials');
});

it('空 body 省略', function (string $body) {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => '/v2/checkout/orders/ORDER_123/capture',
        '_body' => $body,
        '_access_token' => 'test_token_abc',
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getRadar()->getBody()->getContents())->toEqual('');
})->with([
        'empty string' => [''],
        'empty json array' => ['[]'],
        'empty json object' => ['{}']
    ]);

