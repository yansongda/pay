<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints\SetCallbackPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new SetCallbackPlugin();
    $payload = [
        'url' => 'yansongda',
    ];

    $rocket = new Rocket();
    $rocket->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/merchant-service/complaint-notifications',
        '_service_url' => '/v3/merchant-service/complaint-notifications',
        'url' => 'yansongda',
    ]);
});

