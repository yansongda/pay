<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new AddRadarPlugin();
    $params = [];
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => 'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi',
        '_body' => '123',
    ]);

    $rocket = (new Rocket())->setParams($params)->setPayload($payload);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $radar = $result->getRadar();

    expect($radar->hasHeader('Authorization'))->toBeFalse();
    expect($radar->hasHeader('Wechatpay-Serial'))->toBeFalse();
    expect($radar->getHeaderLine('Accept'))->toEqual('application/json, text/plain, application/x-gzip');
    expect($radar->getHeaderLine('User-Agent'))->toEqual('yansongda/pay-v3');
    expect($radar->getHeaderLine('Content-Type'))->toEqual('application/json; charset=utf-8');
    expect((string) $radar->getBody())->toEqual('123');
    expect($radar->getMethod())->toEqual('POST');
    expect((string) $radar->getUri())->toEqual('https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi');
});

it('携带授权的正常场景', function () {
    $plugin = new AddRadarPlugin();
    $params = [];
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => 'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi',
        '_body' => '123',
        '_authorization' => '456',
    ]);

    $rocket = (new Rocket())->setParams($params)->setPayload($payload);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $radar = $result->getRadar();

    expect($radar->hasHeader('Authorization'))->toBeTrue();
    expect($radar->hasHeader('Wechatpay-Serial'))->toBeFalse();
    expect((string) $radar->getBody())->toEqual('123');
    expect($radar->getMethod())->toEqual('POST');
    expect((string) $radar->getUri())->toEqual('https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi');
});

it('携带微信序列号的正常场景', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => 'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi',
        '_body' => '123',
        '_authorization' => '456',
        '_serial_no' => 'yansongda',
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $radar = $result->getRadar();

    expect($radar->getHeaderLine('Wechatpay-Serial'))->toEqual('yansongda');
});

it('携带 content type 的正常场景', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => 'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi',
        '_body' => '123',
        '_content_type' => 'yansongda',
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $radar = $result->getRadar();

    expect($radar->getHeaderLine('Content-Type'))->toEqual('yansongda');
});

it('携带 accept 的正常场景', function () {
    $plugin = new AddRadarPlugin();
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => 'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi',
        '_body' => '123',
        '_accept' => 'yansongda',
    ]);

    $rocket = (new Rocket())->setParams([])->setPayload($payload);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $radar = $result->getRadar();

    expect($radar->getHeaderLine('Accept'))->toEqual('yansongda');
});

