<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Jsb\Pay\Scan\QueryPlugin;

it('正常', function () {
    $plugin = new QueryPlugin();
    $rocket = (new Rocket())
        ->setParams([]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    expect($result->getPayload()->toJson())->toContain('payCheck');
    expect($result->getPayload()->toJson())->toContain('deviceNo');
});

