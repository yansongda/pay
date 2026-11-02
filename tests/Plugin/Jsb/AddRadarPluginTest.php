<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Jsb\AddRadarPlugin;
use Yansongda\Supports\Collection;

it('radar POST 正常', function () {
    $plugin = new AddRadarPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection(['name' => 'yansongda']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect((string) $result->getRadar()->getUri())->toEqual('https://epaytest.jsbchina.cn:9999/eis/merchant/merchantServices.htm');
    expect((string) $result->getRadar()->getBody())->toContain('name=yansongda');
    expect($result->getRadar()->getMethod())->toEqual('POST');

    // 是否存在Content-Type和User-Agent
    expect($result->getRadar()->getHeaders())->toHaveKey('Content-Type');
    expect($result->getRadar()->getHeaders())->toHaveKey('User-Agent');
    // 验证值
    expect($result->getRadar()->getHeader('Content-Type')[0])->toEqual('text/html');
    expect($result->getRadar()->getHeader('User-Agent')[0])->toEqual('yansongda/pay-v3');
});

