<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Coupons\QueryUserPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new QueryUserPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 根据商户号查用户的券，参数缺少 `openid`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('普通参数', function () {
    $plugin = new QueryUserPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => '111',
        'appid' => '222',
        '_t' => 'a',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/marketing/favor/users/111/coupons?appid=222',
        '_service_url' => '/v3/marketing/favor/users/111/coupons?appid=222',
    ]);
});

it('正常', function () {
    $plugin = new QueryUserPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => '111',
        '_t' => 'a',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/marketing/favor/users/111/coupons?appid=wx55955316af4ef13',
        '_service_url' => '/v3/marketing/favor/users/111/coupons?appid=wx55955316af4ef13',
    ]);
});

