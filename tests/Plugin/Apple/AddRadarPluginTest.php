<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Apple\AddRadarPlugin;
use Yansongda\Supports\Collection;

it('沙箱 URL 的普通 GET', function () {
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

it('携带 JWT 的授权', function () {
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

it('无 jwt 标记时不生成 jwt', function () {
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

it('自定义请求头覆盖 content type', function () {
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

it('method 与 body 透传', function () {
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

it('HTTP URL 透传', function () {
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

it('URL 已有查询串时用 & 追加', function () {
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

it('URL 无查询串时用 ? 追加', function () {
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

it('查询过滤空值', function () {
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

