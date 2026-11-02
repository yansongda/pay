<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Member\Authorization\AuthPlugin;

it('正常', function () {
    $plugin = new AuthPlugin();
    $rocket = (new Rocket())
        ->setParams([]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload()->toJson();

    expect($result->getDirection())->not->toEqual(ResponseDirection::class);
    expect($payload)->toContain('alipay.user.info.auth');
});

