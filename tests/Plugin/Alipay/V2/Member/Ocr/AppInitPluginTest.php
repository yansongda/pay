<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Member\Ocr\AppInitPlugin;

it('正常', function () {
    $plugin = new AppInitPlugin();
    $rocket = (new Rocket())
        ->setParams([]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDirection())->not->toEqual(ResponseDirection::class);
    expect($result->getPayload()->toJson())->toContain('datadigital.fincloud.generalsaas.ocr.mobile.initialize');
    expect($result->getPayload()->toJson())->toContain('DATA_DIGITAL_BIZ_CODE_OCR');
});

