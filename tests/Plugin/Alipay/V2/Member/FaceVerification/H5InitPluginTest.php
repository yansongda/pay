<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Member\FaceVerification\H5InitPlugin;

it('正常', function () {
    $plugin = new H5InitPlugin();
    $rocket = (new Rocket())
        ->setParams([]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDirection())->not->toEqual(ResponseDirection::class);
    expect($result->getPayload()->toJson())->toContain('datadigital.fincloud.generalsaas.face.certify.initialize');
    expect($result->getPayload()->toJson())->toContain('FUTURE_TECH_BIZ_FACE_SDK');
});

