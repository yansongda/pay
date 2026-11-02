<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\CancelPlugin;
use Yansongda\Supports\Collection;

it('缺少 ID', function () {

    (new CancelPlugin())->assembly((new Rocket())->setPayload(new Collection([])), fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $result = (new CancelPlugin())->assembly(
        (new Rocket())->setPayload(new Collection(['payment_intent_id' => 'int_test123'])),
        fn ($rocket) => $rocket
    );

    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('/api/v1/pa/payment_intents/int_test123/cancel');
    expect($result->getPayload()->get('request_id'))->not->toBeEmpty();
    expect($result->getPayload()->has('cancellation_reason'))->toBeFalse();
});

it('使用 id 与撤销原因', function () {
    $result = (new CancelPlugin())->assembly(
        (new Rocket())->setPayload(new Collection([
            'id' => 'int_test456',
            'request_id' => 'req_test456',
            'cancellation_reason' => 'requested_by_customer',
        ])),
        fn ($rocket) => $rocket
    );

    expect($result->getPayload()->get('_url'))->toEqual('/api/v1/pa/payment_intents/int_test456/cancel');
    expect($result->getPayload()->get('request_id'))->toEqual('req_test456');
    expect($result->getPayload()->get('cancellation_reason'))->toEqual('requested_by_customer');
});

