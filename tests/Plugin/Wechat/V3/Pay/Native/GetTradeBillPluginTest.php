<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\GetTradeBillPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new GetTradeBillPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: Native 申请交易账单，参数为空', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new GetTradeBillPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'download_url' => '111',
        '_t' => 'a',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/bill/tradebill?download_url=111',
        '_service_url' => '/v3/bill/tradebill?download_url=111',
    ]);
});

