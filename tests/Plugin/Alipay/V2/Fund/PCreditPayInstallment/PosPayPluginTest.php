<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\PCreditPayInstallment\PosPayPlugin;

it('正常', function () {
    $plugin = new PosPayPlugin();
    $rocket = (new Rocket())
        ->setParams([]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload()->toJson();

    expect($result->getDirection())->not->toEqual(ResponseDirection::class);
    expect($payload)->toContain('alipay.trade.pay');
    expect($payload)->toContain('FACE_TO_FACE_PAYMENT');
    expect($payload)->toContain('bar_code');
});

