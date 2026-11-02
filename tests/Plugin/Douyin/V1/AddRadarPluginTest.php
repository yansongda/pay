<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Douyin\V1\AddRadarPlugin;
use Yansongda\Supports\Collection;

it('不带 access token 的正常场景', function () {
    $plugin = new AddRadarPlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);
    $rocket->setPayload(new Collection([
        '_url' => '/api/trade/v2/create/order',
        'out_order_no' => '20240101123456',
        'total_amount' => 1,
        'subject' => '测试商品',
    ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $radar = $result->getRadar();

    expect($radar->getMethod())->toEqual('POST');
    expect((string) $radar->getUri())->toEqual('https://open-sandbox.douyin.com/api/trade/v2/create/order');
    expect($radar->getHeaderLine('Content-Type'))->toEqual('application/json');
    expect($radar->getHeaderLine('User-Agent'))->toEqual('yansongda/pay-v3');
    expect($radar->hasHeader('access-token'))->toBeFalse();
    expect((string) $radar->getBody())->toEqual(json_encode([
        'out_order_no' => '20240101123456',
        'total_amount' => 1,
        'subject' => '测试商品',
    ], JSON_UNESCAPED_UNICODE));
});

it('access token 请求头与 body 优先级', function () {
    $plugin = new AddRadarPlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);
    $rocket->setPayload(new Collection([
        '_url' => '/api/trade/v2/refund/create/refund',
        '_access_token' => 'clt.abc123',
        '_body' => '{"out_order_no":"20240101123456","refund_amount":1}',
        'ignored_field' => 'not_in_body',
    ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $radar = $result->getRadar();

    expect($radar->getMethod())->toEqual('POST');
    expect($radar->getHeaderLine('access-token'))->toEqual('clt.abc123');
    // `_body` 优先，业务字段不再重复打包
    expect((string) $radar->getBody())->toEqual('{"out_order_no":"20240101123456","refund_amount":1}');
});

it('payload 仅含下划线键时 body 为空', function () {
    $plugin = new AddRadarPlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);
    $rocket->setPayload(new Collection(['_url' => '/api/trade/v2/query/order']));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $radar = $result->getRadar();

    expect($radar->getMethod())->toEqual('POST');
    expect((string) $radar->getUri())->toEqual('https://open-sandbox.douyin.com/api/trade/v2/query/order');
    expect($radar->hasHeader('access-token'))->toBeFalse();
    expect((string) $radar->getBody())->toEqual('');
});

