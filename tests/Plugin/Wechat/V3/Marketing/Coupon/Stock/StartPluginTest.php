<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\StartPlugin;
use Yansongda\Supports\Collection;

it('empty payload', function () {
    $plugin = new StartPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 激活代金券，参数缺少 `stock_id`');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal params', function () {
    $plugin = new StartPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'stock_id' => '111',
        'stock_creator_mchid' => '222',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/marketing/favor/stocks/111/start',
        '_service_url' => '/v3/marketing/favor/stocks/111/start',
        'stock_creator_mchid' => '222',
    ]);
});

it('normal', function () {
    $plugin = new StartPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'stock_id' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/marketing/favor/stocks/111/start',
        '_service_url' => '/v3/marketing/favor/stocks/111/start',
        'stock_creator_mchid' => '1600314069',
    ]);
});

