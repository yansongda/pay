<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Member\DetailPlugin;

it('正常', function () {
    $plugin = new DetailPlugin();
    $rocket = (new Rocket())
        ->setParams(['auth_token' => 'auth_token_value']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDirection())->not->toEqual(ResponseDirection::class);
    expect($result->getPayload()->toJson())->toContain('alipay.user.info.share');
    expect($result->getPayload()->toJson())->toContain('auth_token');
    expect($result->getPayload()->toJson())->toContain('auth_token_value');
});

