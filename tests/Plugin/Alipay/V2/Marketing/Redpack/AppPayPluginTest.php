<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Marketing\Redpack\AppPayPlugin;

it('正常', function () {
    $plugin = new AppPayPlugin();
    $rocket = (new Rocket())
        ->setParams([]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDirection())->toEqual(ResponseDirection::class);
    expect($result->getPayload()->toJson())->toContain('alipay.fund.trans.app.pay');
    expect($result->getPayload()->toJson())->toContain('STD_RED_PACKET');
    expect($result->getPayload()->toJson())->toContain('PERSONAL_PAY');
});

