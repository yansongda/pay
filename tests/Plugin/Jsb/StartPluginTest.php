<?php

declare(strict_types=1);

use Yansongda\Artful\Packer\QueryPacker;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Jsb\StartPlugin;

it('正常', function () {
    $plugin = new StartPlugin();
    $rocket = new Rocket();

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();
    expect($payload->get('partnerId'))->toEqual('6a13eab71c4f4b0aa4757eda6fc59710');
    expect($payload->get('version'))->toEqual('v1.0.0');
    expect($payload->get('charset'))->toEqual('utf-8');
    expect($payload->get('createData'))->toEqual(date('Ymd'));
    expect($payload->get('createTime'))->toEqual(date('His'));
    expect($payload->get('bizDate'))->toEqual(date('Ymd'));
    expect($result->getPacker())->toEqual(QueryPacker::class);
});

