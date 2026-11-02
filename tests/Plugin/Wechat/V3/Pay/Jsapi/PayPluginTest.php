<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\PayPlugin;
use Yansongda\Supports\Collection;

it('empty payload', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: Jsapi 下单，参数为空');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'name' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/pay/transactions/jsapi',
        '_service_url' => '/v3/pay/partner/transactions/jsapi',
        'appid' => 'wx55955316af4ef13',
        'mchid' => '1600314069',
        'notify_url' => 'https://pay.yansongda.cn',
        'name' => 'yansongda',
    ]);
});

it('service params', function () {
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
        '_url' => '/v3/pay/transactions/jsapi',
        '_service_url' => '/v3/pay/partner/transactions/jsapi',
        'sp_appid' => 'wx55955316af4ef13',
        'sp_mchid' => '1600314069',
        'sub_mchid' => '333',
        'notify_url' => '444',
        'name' => 'yansongda',
    ]);
});

it('service', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'name' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/pay/transactions/jsapi',
        '_service_url' => '/v3/pay/partner/transactions/jsapi',
        'sp_appid' => 'wx55955316af4ef13',
        'sp_mchid' => '1600314069',
        'sub_mchid' => '1600314070',
        'notify_url' => 'https://pay.yansongda.cn',
        'name' => 'yansongda',
    ]);
});

