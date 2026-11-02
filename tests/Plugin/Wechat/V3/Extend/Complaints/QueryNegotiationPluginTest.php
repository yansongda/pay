<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints\QueryNegotiationPlugin;
use Yansongda\Supports\Collection;

it('empty complaint id', function () {
    $plugin = new QueryNegotiationPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 查询投诉单协商历史，参数缺少 `complaint_id`');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('query empty', function () {
    $plugin = new QueryNegotiationPlugin();
    $payload = [
        'complaint_id' => 'yansongda',
        '_t' => 'a',
    ];

    $rocket = new Rocket();
    $rocket->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/merchant-service/complaints-v2/yansongda/negotiation-historys',
        '_service_url' => '/v3/merchant-service/complaints-v2/yansongda/negotiation-historys',
    ]);
});

it('query not empty', function () {
    $plugin = new QueryNegotiationPlugin();
    $payload = [
        'complaint_id' => 'yansongda',
        'limit' => 2,
        'offset' => 3,
        '_t' => 'a',
    ];

    $rocket = new Rocket();
    $rocket->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/merchant-service/complaints-v2/yansongda/negotiation-historys?limit=2&offset=3',
        '_service_url' => '/v3/merchant-service/complaints-v2/yansongda/negotiation-historys?limit=2&offset=3',
    ]);
});

