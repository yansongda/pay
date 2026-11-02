<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints\QueryCallbackPlugin;

it('正常', function () {
    $plugin = new QueryCallbackPlugin();
    $rocket = new Rocket();

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/merchant-service/complaint-notifications',
        '_service_url' => '/v3/merchant-service/complaint-notifications',
    ]);
});

