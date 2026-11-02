<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\PayPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $rocket = (new Rocket())->setParams([])->setPayload(new Collection([
        'amount' => 100,
        'currency' => 'USD',
        'merchant_order_id' => 'order_123',
    ]));

    $result = (new PayPlugin())->assembly($rocket, fn ($rocket) => $rocket);
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/api/v1/pa/payment_intents/create');
    expect($payload->get('return_url'))->toEqual('https://pay.yansongda.cn/airwallex/return');
    expect($payload->get('request_id'))->not->toBeEmpty();
});

