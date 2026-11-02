<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\OriginResponseDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\ProfitSharing\DownloadBillPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new DownloadBillPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 下载电子回单，参数缺少 `download_url`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new DownloadBillPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'download_url' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDirection())->toEqual(OriginResponseDirection::class);
    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => 'yansongda',
        '_service_url' => 'yansongda',
    ]);
});

