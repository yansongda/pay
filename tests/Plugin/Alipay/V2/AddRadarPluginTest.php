<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\AddRadarPlugin;
use Yansongda\Supports\Collection;

it('radar post normal', function () {
    $plugin = new AddRadarPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection(['name' => 'yansongda']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect((string) $result->getRadar()->getUri())->toEqual('https://openapi.alipay.com/gateway.do?charset=utf-8');
    expect((string) $result->getRadar()->getBody())->toContain('name=yansongda');
    expect($result->getRadar()->getMethod())->toEqual('POST');
});

it('radar get normal', function () {
    $plugin = new AddRadarPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_method' => 'get'])->setPayload(new Collection(['name' => 'yansongda']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect((string) $result->getRadar()->getUri())->toEqual('https://openapi.alipay.com/gateway.do?charset=utf-8');
    expect($result->getRadar()->getMethod())->toEqual('GET');
});

it('radar headers', function () {
    $plugin = new AddRadarPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection(['name' => 'yansongda']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getRadar()->getHeaderLine('Content-Type'))->toEqual('application/x-www-form-urlencoded');
    expect($result->getRadar()->getHeaderLine('User-Agent'))->toEqual('yansongda/pay-v3');
});

it('radar multipart', function () {
    $plugin = new AddRadarPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_multipart' => [['name' => 'yansongda', 'contents' => 'yansongda']]])->setPayload(new Collection(['name' => 'yansongda']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getRadar()->getHeaderLine('Content-Type'))->toBeEmpty();
});

