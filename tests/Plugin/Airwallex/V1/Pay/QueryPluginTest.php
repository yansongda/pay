<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\QueryPlugin;
use Yansongda\Supports\Collection;

it('missing id', function () {
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

    (new QueryPlugin())->assembly((new Rocket())->setPayload(new Collection([])), fn ($rocket) => $rocket);
});

it('normal', function () {
    $result = (new QueryPlugin())->assembly(
        (new Rocket())->setPayload(new Collection(['id' => 'int_test123'])),
        fn ($rocket) => $rocket
    );

    expect($result->getPayload()->get('_method'))->toEqual('GET');
    expect($result->getPayload()->get('_url'))->toEqual('/api/v1/pa/payment_intents/int_test123');
});

