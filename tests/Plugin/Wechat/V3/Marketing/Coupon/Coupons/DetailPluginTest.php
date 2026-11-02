<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Coupons\DetailPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new DetailPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 查询代金券详情，参数缺少 `openid` 或 `coupon_id`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('普通参数', function () {
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

it('正常', function () {
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

