<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\ProfitSharing\ReturnPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new ReturnPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 缺少分账退回参数', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('普通参数', function () {
    $plugin = new ReturnPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'test' => 'yansongda',
        'return_mchid' => '1111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/profitsharing/return-orders',
        '_service_url' => '/v3/profitsharing/return-orders',
        'test' => 'yansongda',
        'return_mchid' => '1111',
    ]);
});

it('正常', function () {
    $plugin = new ReturnPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'test' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/profitsharing/return-orders',
        '_service_url' => '/v3/profitsharing/return-orders',
        'test' => 'yansongda',
        'return_mchid' => '1600314069',
    ]);
});

it('服务商参数', function () {
    $plugin = new ReturnPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'test' => 'yansongda',
        'return_mchid' => '1111',
        'sub_mchid' => '2222',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/profitsharing/return-orders',
        '_service_url' => '/v3/profitsharing/return-orders',
        'test' => 'yansongda',
        'return_mchid' => '1111',
        'sub_mchid' => '2222',
    ]);
});

it('服务商', function () {
    $plugin = new ReturnPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'test' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/profitsharing/return-orders',
        '_service_url' => '/v3/profitsharing/return-orders',
        'test' => 'yansongda',
        'return_mchid' => '1600314069',
        'sub_mchid' => '1600314070',
    ]);
});

