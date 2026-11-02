<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\QueryDetailPlugin;
use Yansongda\Supports\Collection;

it('empty payload', function () {
    $plugin = new QueryDetailPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 查询代金券批次详情，参数缺少 `stock_id`');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal params', function () {
    $plugin = new QueryDetailPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'stock_id' => '111',
        'stock_creator_mchid' => '222',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/marketing/favor/stocks/111?stock_creator_mchid=222',
        '_service_url' => '/v3/marketing/favor/stocks/111?stock_creator_mchid=222',
    ]);
});

it('normal', function () {
    $plugin = new QueryDetailPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'stock_id' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/marketing/favor/stocks/111?stock_creator_mchid=1600314069',
        '_service_url' => '/v3/marketing/favor/stocks/111?stock_creator_mchid=1600314069',
    ]);
});

