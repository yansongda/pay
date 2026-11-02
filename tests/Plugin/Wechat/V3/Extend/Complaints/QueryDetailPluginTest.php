<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints\QueryDetailPlugin;
use Yansongda\Supports\Collection;

it('空投诉单号', function () {
    $plugin = new QueryDetailPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 查询投诉单详情，参数缺少 `complaint_id`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new QueryDetailPlugin();
    $payload = [
        'complaint_id' => 'yansongda',
    ];

    $rocket = new Rocket();
    $rocket->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/merchant-service/complaints-v2/yansongda',
        '_service_url' => '/v3/merchant-service/complaints-v2/yansongda',
    ]);
});

it('携带加密内容的正常场景', function () {
    $plugin = new QueryDetailPlugin();
    $payload = [
        'complaint_id' => 'yansongda',
    ];

    $rocket = new Rocket();
    $rocket->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) {
        $rocket->setDestination(new Collection([
            'payer_phone' => 'WIesmK+dSJycwdhTTkNmv0Lk2wb9o7NGODovccjhyotNnRkEeh+sxRK1gNSRNMJJgkQ30m4HwcuweSO24mehFeXVNTVAKFVef/3FlHnYDZfE1c3mCLToEef7e8J/Z8TwFH1ecn3t+Jk9ZaBpQKNHdQ0Q8jcL7AnL48h0D9BcZxDekPqX6hNnKfISoKSv4TXFcgvBLFeAe4Q3KM0Snq0N5IvI86D9xZqVg6mY+Gfz0782ymQFxflau6Qxx3mJ+0etHMocNuCdgctVH390XYYMc0u+V2FCJ5cU5h/M/AxzP9ayrEO4l0ftaxL6lP0HjifNrkPcAAb+q9I67UepKO9iGw==',
        ]));

        return $rocket;
    });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/merchant-service/complaints-v2/yansongda',
        '_service_url' => '/v3/merchant-service/complaints-v2/yansongda',
    ]);
    expect($result->getDestination()->all()['payer_phone'])->toEqual('yansongda');
});

it('加密内容错误的正常场景', function () {
    $plugin = new QueryDetailPlugin();
    $payload = [
        'complaint_id' => 'yansongda',
    ];

    $rocket = new Rocket();
    $rocket->setPayload(new Collection($payload));

    $plugin->assembly($rocket, function ($rocket) {
        $rocket->setDestination(new Collection([
            'payer_phone' => 'invalid',
        ]));

        return $rocket;
    });
})->throws(InvalidConfigException::class, null, Exception::DECRYPT_WECHAT_ENCRYPTED_CONTENTS_INVALID);

