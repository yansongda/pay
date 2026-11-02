<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints\QueryImagePlugin;
use Yansongda\Supports\Collection;

it('空 media_id', function () {
    $plugin = new QueryImagePlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 图片请求接口，参数缺少 `media_id`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new QueryImagePlugin();
    $payload = [
        'media_id' => 'yansongda',
    ];

    $rocket = new Rocket();
    $rocket->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/merchant-service/images/yansongda',
        '_service_url' => '/v3/merchant-service/images/yansongda',
    ]);
});

