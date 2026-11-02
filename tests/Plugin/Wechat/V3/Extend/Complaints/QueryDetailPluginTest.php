<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints\QueryDetailPlugin;
use Yansongda\Supports\Collection;

it('empty complaint id', function () {
    $plugin = new QueryDetailPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 查询投诉单详情，参数缺少 `complaint_id`');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal', function () {
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

it('normal with encrypted contents', function () {
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

it('normal with encrypted contents wrong', function () {
    $plugin = new QueryDetailPlugin();
    $payload = [
        'complaint_id' => 'yansongda',
    ];

    $rocket = new Rocket();
    $rocket->setPayload(new Collection($payload));

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::DECRYPT_WECHAT_ENCRYPTED_CONTENTS_INVALID);

    $plugin->assembly($rocket, function ($rocket) {
        $rocket->setDestination(new Collection([
            'payer_phone' => 'invalid',
        ]));

        return $rocket;
    });
});

