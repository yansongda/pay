<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\PCreditPayInstallment\H5PayPlugin;

it('正常', function () {
    $plugin = new H5PayPlugin();
    $rocket = (new Rocket())
        ->setParams([]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDirection())->toEqual(ResponseDirection::class);
    expect($result->getPayload()->toJson())->toContain('alipay.trade.wap.pay');
    expect($result->getPayload()->toJson())->toContain('QUICK_WAP_WAY');
});

