<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\JsapiPayPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new JsapiPayPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: Jsapi合单 下单，参数为空', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('普通参数', function () {
    $plugin = new JsapiPayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'combine_mchid' => '333',
        'combine_appid' => 'yansongdaaa',
        'notify_url' => '444',
        'name' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/combine-transactions/jsapi',
        '_service_url' => '/v3/combine-transactions/jsapi',
        'combine_appid' => 'yansongdaaa',
        'combine_mchid' => '333',
        'notify_url' => '444',
        'name' => 'yansongda',
    ]);
});

it('正常', function () {
    $plugin = new JsapiPayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'name' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/combine-transactions/jsapi',
        '_service_url' => '/v3/combine-transactions/jsapi',
        'combine_appid' => 'wx55955316af4ef13',
        'combine_mchid' => '1600314069',
        'notify_url' => 'https://pay.yansongda.cn',
        'name' => 'yansongda',
    ]);
});

