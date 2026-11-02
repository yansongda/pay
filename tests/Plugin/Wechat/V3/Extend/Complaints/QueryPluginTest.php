<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints\QueryPlugin;
use Yansongda\Supports\Collection;

it('空投诉单号', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 查询投诉单列表，缺少必要参数', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new QueryPlugin();
    $payload = [
        'limit' => 2,
        'offset' => 3,
        'begin_date' => '2021-06-06',
        'end_date' => '2021-06-07',
        '_t' => 'a',
    ];

    $rocket = new Rocket();
    $rocket->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/merchant-service/complaints-v2?limit=2&offset=3&begin_date=2021-06-06&end_date=2021-06-07',
        '_service_url' => '/v3/merchant-service/complaints-v2?limit=2&offset=3&begin_date=2021-06-06&end_date=2021-06-07',
    ]);
});

