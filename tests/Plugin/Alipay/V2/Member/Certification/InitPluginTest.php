<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Member\Certification\InitPlugin;

it('正常', function () {
    $plugin = new InitPlugin();
    $rocket = (new Rocket())
        ->setParams([]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload()->toJson();

    expect($result->getDirection())->not->toEqual(ResponseDirection::class);
    expect($payload)->toContain('alipay.user.certify.open.initialize');
    expect($payload)->toContain('FACE');
});

