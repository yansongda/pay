<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints\UpdateRefundPlugin;
use Yansongda\Supports\Collection;

it('空投诉单号', function () {
    $plugin = new UpdateRefundPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 更新退款审批结果，参数缺少 `complaint_id`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('payload', function () {
    $plugin = new UpdateRefundPlugin();
    $payload = [
        'complaint_id' => 'yansongda',
        'action' => 'APPROVE',
        'name' => 'yansongda',
    ];

    $rocket = new Rocket();
    $rocket->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/merchant-service/complaints-v2/yansongda/update-refund-progress',
        '_service_url' => '/v3/merchant-service/complaints-v2/yansongda/update-refund-progress',
        'action' => 'APPROVE',
        'name' => 'yansongda',
    ]);
});

