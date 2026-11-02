<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\H5\RefundPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: H5 退款申请，参数为空', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('普通参数', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'notify_url' => '111',
        'name' => '222',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/refund/domestic/refunds',
        '_service_url' => '/v3/refund/domestic/refunds',
        'notify_url' => '111',
        'name' => '222',
    ]);
});

it('正常', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_trade_no' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/refund/domestic/refunds',
        '_service_url' => '/v3/refund/domestic/refunds',
        'notify_url' => 'https://pay.yansongda.cn',
        'out_trade_no' => '111',
    ]);
});

it('服务商参数', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'notify_url' => '111',
        'sub_mchid' => '222',
        'name' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/refund/domestic/refunds',
        '_service_url' => '/v3/refund/domestic/refunds',
        'notify_url' => '111',
        'sub_mchid' => '222',
        'name' => 'yansongda',
    ]);
});

it('服务商', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'name' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/refund/domestic/refunds',
        '_service_url' => '/v3/refund/domestic/refunds',
        'name' => 'yansongda',
        'notify_url' => 'https://pay.yansongda.cn',
        'sub_mchid' => '1600314070',
    ]);
});

