<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\PayPlugin;

it('正常', function () {
    $plugin = new PayPlugin();
    $rocket = (new Rocket())
        ->setParams([]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDirection())->toEqual(ResponseDirection::class);
    expect($result->getPayload()->toJson())->toContain('alipay.trade.page.pay');
    expect($result->getPayload()->toJson())->toContain('FAST_INSTANT_TRADE_PAY');
});

