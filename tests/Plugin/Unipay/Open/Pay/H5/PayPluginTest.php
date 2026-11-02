<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\H5\PayPlugin;

it('普通参数', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload([
        'accessType' => '1',
        'bizType' => '2',
        'txnType' => '3',
        'txnSubType' => '4',
        'channelType' => '5',
    ]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($result->getDirection())->toEqual(ResponseDirection::class);
    expect($payload->all())->toEqual([
        '_url' => '/gateway/api/frontTransReq.do',
        'encoding' => 'utf-8',
        'signature' => '',
        'bizType' => '2',
        'accessType' => '1',
        'currencyCode' => '156',
        'merId' => '777290058167151',
        'channelType' => '5',
        'signMethod' => '01',
        'txnType' => '3',
        'txnSubType' => '4',
        'frontUrl' => 'https://pay.yansongda.cn',
        'backUrl' => 'https://pay.yansongda.cn',
        'version' => '5.1.0',
    ]);
});

it('正常', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($result->getDirection())->toEqual(ResponseDirection::class);
    expect($payload->all())->toEqual([
        '_url' => '/gateway/api/frontTransReq.do',
        'encoding' => 'utf-8',
        'signature' => '',
        'bizType' => '000201',
        'accessType' => '0',
        'currencyCode' => '156',
        'merId' => '777290058167151',
        'channelType' => '07',
        'signMethod' => '01',
        'txnType' => '01',
        'txnSubType' => '01',
        'frontUrl' => 'https://pay.yansongda.cn',
        'backUrl' => 'https://pay.yansongda.cn',
        'version' => '5.1.0',
    ]);
});

