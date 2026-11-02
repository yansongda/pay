<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\PausePlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new PausePlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 暂停代金券批次，参数缺少 `stock_id`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('普通参数', function () {
    $plugin = new PausePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'stock_id' => 'yansongda',
        'stock_creator_mchid' => '1111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/marketing/favor/stocks/yansongda/pause',
        '_service_url' => '/v3/marketing/favor/stocks/yansongda/pause',
        'stock_creator_mchid' => '1111',
    ]);
});

it('正常', function () {
    $plugin = new PausePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'stock_id' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/marketing/favor/stocks/yansongda/pause',
        '_service_url' => '/v3/marketing/favor/stocks/yansongda/pause',
        'stock_creator_mchid' => '1600314069',
    ]);
});

