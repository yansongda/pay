<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\QueryUseFlowPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new QueryUseFlowPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 下载批次核销明细，参数缺少 `stock_id`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new QueryUseFlowPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'stock_id' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/marketing/favor/stocks/111/use-flow',
        '_service_url' => '/v3/marketing/favor/stocks/111/use-flow',
    ]);
});

