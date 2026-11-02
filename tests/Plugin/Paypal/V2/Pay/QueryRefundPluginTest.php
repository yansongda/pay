<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Paypal\V2\Pay\QueryRefundPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new QueryRefundPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['refund_id' => 'REF_789'])->setPayload(new Collection([]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('GET');
    expect($payload->get('_url'))->toContain('REF_789');
});

it('无冗余参数的正常场景', function () {
    $plugin = new QueryRefundPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['refund_id' => 'REF_789', 'extra' => 'should_not_stay'])->setPayload(new Collection([]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    // 业务参数不应残留在 payload 中，否则会被打进最终请求 body
    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v2/payments/refunds/REF_789',
    ]);
});

it('缺少退款单号', function () {
    $plugin = new QueryRefundPlugin();

    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection([]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

