<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\ProfitSharing\QueryReturnPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new QueryReturnPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 查询分账结果, 缺少必要参数 `out_order_no`, `out_return_no`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new QueryReturnPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_return_no' => 'yansongda',
        'out_order_no' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/profitsharing/return-orders/yansongda?out_order_no=111',
        '_service_url' => '/v3/profitsharing/return-orders/yansongda?sub_mchid=null&out_order_no=111',
    ]);
});

it('服务商', function () {
    $plugin = new QueryReturnPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'out_return_no' => 'yansongda',
        'out_order_no' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/profitsharing/return-orders/yansongda?out_order_no=111',
        '_service_url' => '/v3/profitsharing/return-orders/yansongda?sub_mchid=1600314070&out_order_no=111',
    ]);
});

it('服务商参数', function () {
    $plugin = new QueryReturnPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'out_return_no' => 'yansongda',
        'out_order_no' => '111',
        'sub_mchid' => '222',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/profitsharing/return-orders/yansongda?out_order_no=111',
        '_service_url' => '/v3/profitsharing/return-orders/yansongda?sub_mchid=222&out_order_no=111',
    ]);
});

