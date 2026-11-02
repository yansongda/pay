<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\OriginResponseDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\DownloadBillPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new DownloadBillPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: Native 下载交易对账单，参数缺少 `download_url`');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('正常', function () {
    $plugin = new DownloadBillPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'download_url' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDirection())->toEqual(OriginResponseDirection::class);
    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '111',
        '_service_url' => '111',
    ]);
});

