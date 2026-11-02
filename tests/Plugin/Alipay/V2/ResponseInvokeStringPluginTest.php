<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\ResponseInvokeStringPlugin;

it('normal', function () {
    $plugin = new ResponseInvokeStringPlugin();
    $payload = [
        'name' => 'yansongda',
        'age' => 30,
    ];

    $rocket = new Rocket();
    $rocket->mergePayload($payload);

    $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

    expect($result->getDestination()->getBody()->getContents())->toEqual(http_build_query($payload));
});

