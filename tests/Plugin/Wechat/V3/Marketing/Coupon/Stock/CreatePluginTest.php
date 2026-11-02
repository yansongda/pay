<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\CreatePlugin;
use Yansongda\Supports\Collection;

it('普通参数', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'test' => 'yansongda',
        'belong_merchant' => '1111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/marketing/favor/coupon-stocks',
        '_service_url' => '/v3/marketing/favor/coupon-stocks',
        'test' => 'yansongda',
        'belong_merchant' => '1111',
    ]);
});

it('正常', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'test' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/marketing/favor/coupon-stocks',
        '_service_url' => '/v3/marketing/favor/coupon-stocks',
        'test' => 'yansongda',
        'belong_merchant' => '1600314069',
    ]);
});

