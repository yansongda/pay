<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Callback\SetPlugin;
use Yansongda\Supports\Collection;

it('普通参数', function () {
    $plugin = new SetPlugin();
    $payload = [
        'mchid' => 'yansongda',
        'notify_url' => 'https://www.yansongda.cn',
        'test' => 'aaa',
    ];

    $rocket = new Rocket();
    $rocket->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/marketing/favor/callbacks',
        '_service_url' => '/v3/marketing/favor/callbacks',
        'mchid' => 'yansongda',
        'notify_url' => 'https://www.yansongda.cn',
        'test' => 'aaa',
    ]);
});

it('正常', function () {
    $plugin = new SetPlugin();
    $payload = [
        'test' => 'aaa',
    ];

    $rocket = new Rocket();
    $rocket->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/marketing/favor/callbacks',
        '_service_url' => '/v3/marketing/favor/callbacks',
        'mchid' => '1600314069',
        'notify_url' => 'https://pay.yansongda.cn',
        'test' => 'aaa',
    ]);
});

