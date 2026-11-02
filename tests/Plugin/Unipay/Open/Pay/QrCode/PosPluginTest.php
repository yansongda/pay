<?php

declare(strict_types=1);

use Yansongda\Artful\Packer\QueryPacker;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\PosPlugin;

it('普通参数', function () {
    $plugin = new PosPlugin();
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

    expect($result->getPacker())->toEqual(QueryPacker::class);
    expect($payload->all())->toEqual([
        '_url' => '/gateway/api/backTransReq.do',
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
        'backUrl' => 'https://pay.yansongda.cn',
        'version' => '5.1.0',
    ]);
});

it('正常', function () {
    $plugin = new PosPlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($result->getPacker())->toEqual(QueryPacker::class);
    expect($payload->all())->toEqual([
        '_url' => '/gateway/api/backTransReq.do',
        'encoding' => 'utf-8',
        'signature' => '',
        'bizType' => '000000',
        'accessType' => '0',
        'currencyCode' => '156',
        'merId' => '777290058167151',
        'channelType' => '08',
        'signMethod' => '01',
        'txnType' => '01',
        'txnSubType' => '06',
        'backUrl' => 'https://pay.yansongda.cn',
        'version' => '5.1.0',
    ]);
});

