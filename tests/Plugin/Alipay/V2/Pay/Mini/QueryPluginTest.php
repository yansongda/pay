<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\QueryPlugin;

it('正常', function () {
    $plugin = new QueryPlugin();
    $rocket = (new Rocket())
        ->setParams([]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDirection())->not->toEqual(ResponseDirection::class);
    expect($result->getPayload()->toJson())->toContain('alipay.trade.query');
});

