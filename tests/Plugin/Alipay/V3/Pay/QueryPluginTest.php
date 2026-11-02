<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new QueryPlugin();
    $rocket = (new Rocket())
        ->setParams(['_config' => 'alipay-v3', 'out_trade_no' => 'yansongda-2026'])
        ->setPayload(new Collection(['_config' => 'alipay-v3', 'out_trade_no' => 'yansongda-2026']));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('/v3/alipay/trade/query');

    // 业务字段原样透传，插件不干预（对齐 V2 查询条件语义：out_trade_no/trade_no 二选一由调用方决定）
    expect($result->getPayload()->get('out_trade_no'))->toEqual('yansongda-2026');
    expect($result->getPayload()->get('notify_url'))->toBeNull();
});

it('携带交易号的正常场景', function () {
    $plugin = new QueryPlugin();
    $rocket = (new Rocket())
        ->setParams(['_config' => 'alipay-v3', 'trade_no' => '2026090222001400001234567890'])
        ->setPayload(new Collection(['_config' => 'alipay-v3', 'trade_no' => '2026090222001400001234567890']));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('/v3/alipay/trade/query');
    expect($result->getPayload()->get('trade_no'))->toEqual('2026090222001400001234567890');
});

