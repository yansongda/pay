<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\PayPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: Native 下单，参数为空', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'name' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/pay/transactions/native',
        '_service_url' => '/v3/pay/partner/transactions/native',
        'appid' => 'wx55955316af4ef13',
        'mchid' => '1600314069',
        'notify_url' => 'https://pay.yansongda.cn',
        'name' => 'yansongda',
    ]);
});

it('服务商参数', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'sub_mchid' => '333',
        'notify_url' => '444',
        'name' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/pay/transactions/native',
        '_service_url' => '/v3/pay/partner/transactions/native',
        'sp_appid' => 'wx55955316af4ef13',
        'sp_mchid' => '1600314069',
        'sub_mchid' => '333',
        'notify_url' => '444',
        'name' => 'yansongda',
    ]);
});

it('服务商', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'name' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/pay/transactions/native',
        '_service_url' => '/v3/pay/partner/transactions/native',
        'sp_appid' => 'wx55955316af4ef13',
        'sp_mchid' => '1600314069',
        'sub_mchid' => '1600314070',
        'notify_url' => 'https://pay.yansongda.cn',
        'name' => 'yansongda',
    ]);
});

