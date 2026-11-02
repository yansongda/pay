<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\CancelPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new CancelPlugin();
    $params = ['_config' => 'alipay-v3', 'out_trade_no' => 'yansongda-2026'];
    $rocket = (new Rocket())->setParams($params)->setPayload(new Collection($params));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('/v3/alipay/trade/cancel');

    // 业务字段原样透传（官方 AlipayTradeCancelModel 无 notify_url 字段，不注入）
    expect($result->getPayload()->get('out_trade_no'))->toEqual('yansongda-2026');
    expect($result->getPayload()->get('notify_url'))->toBeNull();
});

