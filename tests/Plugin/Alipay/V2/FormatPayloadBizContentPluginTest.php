<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\FormatPayloadBizContentPlugin;
use Yansongda\Supports\Collection;

it('sign normal', function () {
    $plugin = new FormatPayloadBizContentPlugin();
    $payload = [
        'biz_content' => ['out_trade_no' => 'yansongda-1622986519'],
    ];

    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('biz_content'))->toEqual('{"out_trade_no":"yansongda-1622986519"}');
});

it('sign underline params', function () {
    $plugin = new FormatPayloadBizContentPlugin();
    $payload = [
        'biz_content' => ['out_trade_no' => 'yansongda-1622986519', '_method' => 'get', '_ignore' => true],
    ];

    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('biz_content'))->toEqual('{"out_trade_no":"yansongda-1622986519"}');
});

