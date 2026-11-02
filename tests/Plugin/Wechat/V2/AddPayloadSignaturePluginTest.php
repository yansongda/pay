<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\V2\AddPayloadSignaturePlugin;
use Yansongda\Supports\Collection;

it('获取待签名内容', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $payload1 = new Collection([
        '_method' => 'POST',
        '_url' => 'yansongda',
        'a' => null,
        'out_trade_no' => 1626493236,
        'description' => 'yansongda 测试 - 1626493236',
    ]);
    $rocket1 = (new Rocket())->setPayload($payload1);
    $result1 = $plugin->assembly($rocket1, fn ($rocket) => $rocket);

    $payload2 = new Collection([
        'out_trade_no' => 1626493236,
        'description' => 'yansongda 测试 - 1626493236',
    ]);
    $rocket2 = (new Rocket())->setPayload($payload2);
    $result2 = $plugin->assembly($rocket2, fn ($rocket) => $rocket);

    expect($result1->getPayload()->get('sign'))->toEqual($result2->getPayload()->get('sign'));
});

