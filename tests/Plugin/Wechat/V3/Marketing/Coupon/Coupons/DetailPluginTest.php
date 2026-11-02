<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Coupons\DetailPlugin;
use Yansongda\Supports\Collection;

it('empty payload', function () {
    $plugin = new DetailPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 查询代金券详情，参数缺少 `openid` 或 `coupon_id`');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal params', function () {
    $plugin = new DetailPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => '111',
        'coupon_id' => '222',
        'appid' => '333',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/marketing/favor/users/111/coupons/222?appid=333',
        '_service_url' => '/v3/marketing/favor/users/111/coupons/222?appid=333',
    ]);
});

it('normal', function () {
    $plugin = new DetailPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => '111',
        'coupon_id' => '222',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/marketing/favor/users/111/coupons/222?appid=wx55955316af4ef13',
        '_service_url' => '/v3/marketing/favor/users/111/coupons/222?appid=wx55955316af4ef13',
    ]);
});

