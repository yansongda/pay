<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\ConfirmPlugin;
use Yansongda\Supports\Collection;

it('missing id', function () {
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

    (new ConfirmPlugin())->assembly((new Rocket())->setPayload(new Collection([])), fn ($rocket) => $rocket);
});

it('normal', function () {
    $result = (new ConfirmPlugin())->assembly(
        (new Rocket())->setPayload(new Collection([
            'payment_intent_id' => 'int_test123',
            'payment_method' => ['type' => 'card'],
        ])),
        fn ($rocket) => $rocket
    );

    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('/api/v1/pa/payment_intents/int_test123/confirm');
    expect($result->getPayload()->get('request_id'))->not->toBeEmpty();
});

