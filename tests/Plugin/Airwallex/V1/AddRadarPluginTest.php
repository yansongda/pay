<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Airwallex\V1\AddRadarPlugin;
use Yansongda\Supports\Collection;

it('登录请求', function () {
    $plugin = new AddRadarPlugin();
    $rocket = (new Rocket())->setParams([])->setPayload(new Collection([
        '_method' => 'POST',
        '_url' => '/api/v1/authentication/login',
        '_auth_type' => 'client',
    ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $radar = $result->getRadar();

    expect($radar->getHeaderLine('x-client-id'))->toEqual('airwallex_client_id');
    expect($radar->getHeaderLine('x-api-key'))->toEqual('airwallex_api_key');
    expect($radar->getHeaderLine('x-api-version'))->toEqual('2024-06-14');
});

it('Bearer 请求', function () {
    $plugin = new AddRadarPlugin();
    $rocket = (new Rocket())->setParams([])->setPayload(new Collection([
        '_method' => 'GET',
        '_url' => '/api/v1/pa/payment_intents/int_test123',
        '_access_token' => 'airwallex_access_token',
    ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $radar = $result->getRadar();

    expect($radar->getHeaderLine('Authorization'))->toEqual('Bearer airwallex_access_token');
    expect((string) $radar->getUri())->toContain('api-demo.airwallex.com');
});

it('代调用与 body', function () {
    $plugin = new AddRadarPlugin();
    $rocket = (new Rocket())->setParams([])->setPayload(new Collection([
        '_method' => 'POST',
        '_url' => '/api/v1/pa/payment_intents/create',
        '_access_token' => 'airwallex_access_token',
        '_on_behalf_of' => 'acct_123',
        '_body' => '{"amount":100}',
    ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $radar = $result->getRadar();

    expect($radar->getHeaderLine('x-on-behalf-of'))->toEqual('acct_123');
    expect((string) $radar->getBody())->toEqual('{"amount":100}');
});

