<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Unipay\Open\StartPlugin;

it('正常', function () {
    $plugin = new StartPlugin();
    $params = [
        'txnTime' => '20220903065448',
        'txnAmt' => 1,
        'orderId' => 'yansongda20220903065448',
    ];
    $payload = array_merge($params, [
        '_unpack_raw' => true,
        'certId' => '69903319369',
    ]);

    $rocket = (new Rocket())->setParams($params);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual($payload);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect(true)->toBeTrue();
});

it('证书已缓存', function () {
    $plugin = new StartPlugin();
    $result = $plugin->assembly(new Rocket(), function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('certId'))->toEqual('69903319369');
});

