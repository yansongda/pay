<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\QueryRefundPlugin;
use Yansongda\Supports\Collection;

it('缺少 ID', function () {

    (new QueryRefundPlugin())->assembly((new Rocket())->setPayload(new Collection([])), fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $result = (new QueryRefundPlugin())->assembly(
        (new Rocket())->setPayload(new Collection(['refund_id' => 'ref_test123'])),
        fn ($rocket) => $rocket
    );

    expect($result->getPayload()->get('_method'))->toEqual('GET');
    expect($result->getPayload()->get('_url'))->toEqual('/api/v1/pa/refunds/ref_test123');
});

it('使用 id', function () {
    $result = (new QueryRefundPlugin())->assembly(
        (new Rocket())->setPayload(new Collection(['id' => 'ref_test456'])),
        fn ($rocket) => $rocket
    );

    expect($result->getPayload()->get('_url'))->toEqual('/api/v1/pa/refunds/ref_test456');
});

