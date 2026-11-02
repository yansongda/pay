<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\ProfitSharing\DeleteReceiverPlugin;
use Yansongda\Supports\Collection;

it('empty payload', function () {
    $plugin = new DeleteReceiverPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 缺少分账参数');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal', function () {
    $plugin = new DeleteReceiverPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'test' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/profitsharing/receivers/delete',
        '_service_url' => '/v3/profitsharing/receivers/delete',
        'test' => 'yansongda',
        'appid' => 'wx55955316af4ef13',
    ]);
});

it('service params', function () {
    $plugin = new DeleteReceiverPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'test' => 'yansongda',
        'sub_mchid' => '2222',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/profitsharing/receivers/delete',
        '_service_url' => '/v3/profitsharing/receivers/delete',
        'test' => 'yansongda',
        'appid' => 'wx55955316af4ef13',
        'sub_mchid' => '2222',
    ]);
});

it('service', function () {
    $plugin = new DeleteReceiverPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'test' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/profitsharing/receivers/delete',
        '_service_url' => '/v3/profitsharing/receivers/delete',
        'test' => 'yansongda',
        'appid' => 'wx55955316af4ef13',
        'sub_mchid' => '1600314070',
    ]);
});

