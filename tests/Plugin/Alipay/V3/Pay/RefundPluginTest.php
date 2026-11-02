<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\RefundPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new RefundPlugin();
    $params = [
        '_config' => 'alipay-v3',
        'out_trade_no' => 'yansongda-2026',
        'out_request_no' => 'yansongda-2026-refund',
        'refund_amount' => '3.00',
        'refund_reason' => '正常退款',
    ];
    $rocket = (new Rocket())->setParams($params)->setPayload(new Collection($params));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('/v3/alipay/trade/refund');

    // 业务字段原样透传（官方 AlipayTradeRefundModel 无 notify_url 字段，不注入）
    expect($result->getPayload()->get('out_trade_no'))->toEqual('yansongda-2026');
    expect($result->getPayload()->get('out_request_no'))->toEqual('yansongda-2026-refund');
    expect($result->getPayload()->get('refund_amount'))->toEqual('3.00');
    expect($result->getPayload()->get('refund_reason'))->toEqual('正常退款');
    expect($result->getPayload()->get('notify_url'))->toBeNull();
});

