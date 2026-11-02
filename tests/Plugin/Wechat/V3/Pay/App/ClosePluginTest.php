<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\OriginResponseDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\App\ClosePlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new ClosePlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: App 关闭订单，参数缺少 `out_trade_no`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new ClosePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_trade_no' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/pay/transactions/out-trade-no/111/close',
        '_service_url' => '/v3/pay/partner/transactions/out-trade-no/111/close',
        'mchid' => '1600314069',
    ]);
    expect($result->getDirection())->toEqual(OriginResponseDirection::class);
});

it('服务商参数', function () {
    $plugin = new ClosePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'out_trade_no' => '111',
        'sub_mchid' => '333',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/pay/transactions/out-trade-no/111/close',
        '_service_url' => '/v3/pay/partner/transactions/out-trade-no/111/close',
        'sp_mchid' => '1600314069',
        'sub_mchid' => '333',
    ]);
    expect($result->getDirection())->toEqual(OriginResponseDirection::class);
});

it('服务商', function () {
    $plugin = new ClosePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'out_trade_no' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/pay/transactions/out-trade-no/111/close',
        '_service_url' => '/v3/pay/partner/transactions/out-trade-no/111/close',
        'sp_mchid' => '1600314069',
        'sub_mchid' => '1600314070',
    ]);
    expect($result->getDirection())->toEqual(OriginResponseDirection::class);
});

