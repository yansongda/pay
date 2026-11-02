<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Unipay\Qra\StartPlugin;

it('正常', function () {
    $plugin = new StartPlugin();
    $params = [
        'txnTime' => '20220903065448',
        'txnAmt' => 1,
        'orderId' => 'yansongda20220903065448',
    ];

    $rocket = (new Rocket())->setParams($params);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual($params);
});

