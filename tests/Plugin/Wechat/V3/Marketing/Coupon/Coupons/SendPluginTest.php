<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Coupons\SendPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new SendPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 发放指定批次的代金券，参数缺少 `openid`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('普通参数', function () {
    $plugin = new SendPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => '111',
        'appid' => '222',
        'stock_creator_mchid' => '333',
        'test' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/marketing/favor/users/111/coupons',
        '_service_url' => '/v3/marketing/favor/users/111/coupons',
        'appid' => '222',
        'stock_creator_mchid' => '333',
        'test' => 'yansongda',
    ]);
});

it('正常', function () {
    $plugin = new SendPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => '111',
        'test' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/marketing/favor/users/111/coupons',
        '_service_url' => '/v3/marketing/favor/users/111/coupons',
        'appid' => 'wx55955316af4ef13',
        'stock_creator_mchid' => '1600314069',
        'test' => 'yansongda',
    ]);
});

