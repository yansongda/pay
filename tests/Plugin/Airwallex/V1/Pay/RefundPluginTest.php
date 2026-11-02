<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\RefundPlugin;
use Yansongda\Supports\Collection;

it('缺少 payment_intent_id', function () {

    (new RefundPlugin())->assembly((new Rocket())->setPayload(new Collection([])), fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $result = (new RefundPlugin())->assembly(
        (new Rocket())->setPayload(new Collection([
            'payment_intent_id' => 'int_test123',
            'amount' => 66.6,
        ])),
        fn ($rocket) => $rocket
    );

    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('/api/v1/pa/refunds/create');
    expect($result->getPayload()->get('payment_intent_id'))->toEqual('int_test123');
});

it('使用 id 与可选字段', function () {
    $result = (new RefundPlugin())->assembly(
        (new Rocket())->setPayload(new Collection([
            'id' => 'int_test456',
            'request_id' => 'req_refund_456',
            'reason' => 'requested_by_customer',
            'metadata' => ['biz_order_id' => 'biz_123'],
        ])),
        fn ($rocket) => $rocket
    );

    expect($result->getPayload()->get('payment_intent_id'))->toEqual('int_test456');
    expect($result->getPayload()->get('request_id'))->toEqual('req_refund_456');
    expect($result->getPayload()->get('reason'))->toEqual('requested_by_customer');
    expect($result->getPayload()->get('metadata'))->toEqual(['biz_order_id' => 'biz_123']);
});

