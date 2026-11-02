<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Auth\ScanFreezePlugin;

it('正常', function () {
    $plugin = new ScanFreezePlugin();
    $rocket = (new Rocket())
        ->setParams([]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDirection())->not->toEqual(ResponseDirection::class);
    expect($result->getPayload()->toJson())->toContain('alipay.fund.auth.order.voucher.create');
    expect($result->getPayload()->toJson())->toContain('PREAUTH_PAY');
});

