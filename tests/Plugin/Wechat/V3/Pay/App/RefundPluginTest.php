<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\App\RefundPlugin;
use Yansongda\Supports\Collection;

it('empty payload', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: App 退款申请，参数为空');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal params', function () {
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

it('normal', function () {
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

it('service params', function () {
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

it('service', function () {
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

