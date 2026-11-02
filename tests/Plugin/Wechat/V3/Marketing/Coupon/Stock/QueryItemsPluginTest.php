<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\QueryItemsPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new QueryItemsPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 查询代金券可用单品，参数缺少 `stock_id`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('普通参数', function () {
    $plugin = new QueryItemsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'stock_id' => '111',
        'stock_creator_mchid' => '222',
        '_t' => 'a',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/marketing/favor/stocks/111/items?stock_creator_mchid=222',
        '_service_url' => '/v3/marketing/favor/stocks/111/items?stock_creator_mchid=222',
    ]);
});

it('正常', function () {
    $plugin = new QueryItemsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'stock_id' => '111',
        '_t' => 'a',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/marketing/favor/stocks/111/items?stock_creator_mchid=1600314069',
        '_service_url' => '/v3/marketing/favor/stocks/111/items?stock_creator_mchid=1600314069',
    ]);
});

