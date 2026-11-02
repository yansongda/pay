<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\OriginResponseDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\ClosePlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new ClosePlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 合单关单，参数缺少 `combine_out_trade_no`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('普通参数', function () {
    $plugin = new ClosePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'combine_out_trade_no' => '111',
        'combine_appid' => '333',
        'name' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/combine-transactions/out-trade-no/111/close',
        '_service_url' => '/v3/combine-transactions/out-trade-no/111/close',
        'combine_appid' => '333',
        'name' => 'yansongda',
    ]);
    expect($result->getDirection())->toEqual(OriginResponseDirection::class);
});

it('正常', function () {
    $plugin = new ClosePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'combine_out_trade_no' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/combine-transactions/out-trade-no/111/close',
        '_service_url' => '/v3/combine-transactions/out-trade-no/111/close',
        'combine_appid' => 'wx55955316af4ef13',
    ]);
    expect($result->getDirection())->toEqual(OriginResponseDirection::class);
});

