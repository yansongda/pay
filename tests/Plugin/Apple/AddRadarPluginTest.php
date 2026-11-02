<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Apple\AddRadarPlugin;
use Yansongda\Supports\Collection;

it('normal get with sandbox url', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'GET',
        '_url' => '/inApps/v1/transactions/123',
        '_no_jwt' => true,
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $radar = $result->getRadar();

    expect($radar->getMethod())->toEqual('GET');
    expect((string) $radar->getUri())->toEqual('https://api.storekit-sandbox.apple.com/inApps/v1/transactions/123');
    expect($radar->getHeaderLine('Content-Type'))->toEqual('application/json');
});

it('authorization with jwt', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'GET',
        '_url' => '/inApps/v1/transactions/123',
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $radar = $result->getRadar();

    $authorization = $radar->getHeaderLine('Authorization');

    expect($authorization)->toStartWith('Bearer ');
    expect(explode('.', substr($authorization, 7)))->toHaveCount(3);
});

it('no jwt when no jwt flag', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'GET',
        '_url' => '/inApps/v1/transactions/123',
        '_no_jwt' => true,
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $radar = $result->getRadar();

    expect($radar->hasHeader('Authorization'))->toBeFalse();
});

it('custom headers override content type', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'GET',
        '_url' => '/inApps/v1/transactions/123',
        '_no_jwt' => true,
        '_headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $radar = $result->getRadar();

    expect($radar->getHeaderLine('Content-Type'))->toEqual('application/x-www-form-urlencoded');
});

it('method and body passthrough', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => '/inApps/v1/subscriptions/123',
        '_body' => '{"foo":"bar"}',
        '_no_jwt' => true,
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $radar = $result->getRadar();

    expect($radar->getMethod())->toEqual('POST');
    expect((string) $radar->getBody())->toEqual('{"foo":"bar"}');
});

it('http url passthrough', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => 'https://apple-pay-gateway.apple.com/paymentservices/startSession',
        '_body' => '{"merchantIdentifier":"merchant.com.yansongda.pay"}',
        '_no_jwt' => true,
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $radar = $result->getRadar();

    expect((string) $radar->getUri())->toEqual('https://apple-pay-gateway.apple.com/paymentservices/startSession');
});

it('query appended with ampersand when url has query', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'GET',
        '_url' => '/inApps/v1/subscriptions/123?status=1&status=4',
        '_query' => ['revision' => 'abc'],
        '_no_jwt' => true,
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $radar = $result->getRadar();

    expect((string) $radar->getUri())->toEqual('https://api.storekit-sandbox.apple.com/inApps/v1/subscriptions/123?status=1&status=4&revision=abc');
});

it('query appended with question mark when url has no query', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'GET',
        '_url' => '/inApps/v1/transactions/123',
        '_query' => ['revision' => 'abc'],
        '_no_jwt' => true,
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $radar = $result->getRadar();

    expect((string) $radar->getUri())->toEqual('https://api.storekit-sandbox.apple.com/inApps/v1/transactions/123?revision=abc');
});

it('query filters null values', function () {
    $plugin = new AddRadarPlugin();
    // filter_params 对齐 Stripe：null 值不进入最终 query string
    $payload = new Collection([
        '_method' => 'GET',
        '_url' => '/inApps/v2/history/123',
        '_query' => ['revision' => 'abc', 'startDate' => null, 'endDate' => null],
        '_no_jwt' => true,
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $radar = $result->getRadar();

    expect((string) $radar->getUri())->toEqual('https://api.storekit-sandbox.apple.com/inApps/v2/history/123?revision=abc');
});

