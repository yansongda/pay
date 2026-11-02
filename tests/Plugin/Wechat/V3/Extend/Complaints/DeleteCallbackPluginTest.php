<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints\DeleteCallbackPlugin;

it('正常', function () {
    $plugin = new DeleteCallbackPlugin();
    $rocket = new Rocket();

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'DELETE',
        '_url' => '/v3/merchant-service/complaint-notifications',
        '_service_url' => '/v3/merchant-service/complaint-notifications',
    ]);
});

