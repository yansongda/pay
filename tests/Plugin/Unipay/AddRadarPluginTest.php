<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new AddRadarPlugin();
    $params = [];
    $payload = new Collection([
        '_url' => 'https://pay.yansongda.cn',
        '_body' => '123',
    ]);

    $rocket = (new Rocket())->setParams($params)->setPayload($payload);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $radar = $result->getRadar();

    expect((string) $radar->getBody())->toEqual('123');
    expect($radar->getMethod())->toEqual('POST');
    expect((string) $radar->getUri())->toEqual('https://pay.yansongda.cn');
});

