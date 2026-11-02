<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Jsb\Pay\Scan\RefundPlugin;

it('正常', function () {
    $plugin = new RefundPlugin();
    $rocket = (new Rocket())
        ->setParams([]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    expect($result->getPayload()->toJson())->toContain('payRefund');
    expect($result->getPayload()->toJson())->toContain('deviceNo');
});

