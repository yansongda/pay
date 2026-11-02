<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Openapi\GetStableTokenPlugin;

it('正常', function () {
    $plugin = new GetStableTokenPlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/cgi-bin/stable_token');
});

