<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints\ResponsePlugin;
use Yansongda\Supports\Collection;

it('空投诉单号', function () {
    $plugin = new ResponsePlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 回复用户，参数缺少 `complaint_id`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('payload 中商户号为空', function () {
    $plugin = new ResponsePlugin();
    $payload = [
        'complaint_id' => 'yansongda',
    ];

    $rocket = new Rocket();
    $rocket->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/merchant-service/complaints-v2/yansongda/response',
        '_service_url' => '/v3/merchant-service/complaints-v2/yansongda/response',
        'complainted_mchid' => '1600314069',
    ]);
});

it('payload 中商户号非空', function () {
    $plugin = new ResponsePlugin();
    $payload = [
        'complaint_id' => 'yansongda',
        'complainted_mchid' => '123',
    ];

    $rocket = new Rocket();
    $rocket->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/merchant-service/complaints-v2/yansongda/response',
        '_service_url' => '/v3/merchant-service/complaints-v2/yansongda/response',
        'complainted_mchid' => '123',
    ]);
});

it('payload 其它字段', function () {
    $plugin = new ResponsePlugin();
    $payload = [
        'complaint_id' => 'yansongda',
        'complainted_mchid' => '123',
        'name' => 'yansongda',
        'test' => 'abc',
    ];

    $rocket = new Rocket();
    $rocket->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/merchant-service/complaints-v2/yansongda/response',
        '_service_url' => '/v3/merchant-service/complaints-v2/yansongda/response',
        'complainted_mchid' => '123',
        'name' => 'yansongda',
        'test' => 'abc',
    ]);
});

