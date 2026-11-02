<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\ClosePlugin;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new ClosePlugin();
    $params = [
        '_config' => 'alipay-v3',
        'out_trade_no' => 'yansongda-2026',
        'notify_url' => 'https://pay.yansongda.cn/alipay/notify',
    ];
    $rocket = (new Rocket())->setParams($params)->setPayload(new Collection($params));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('/v3/alipay/trade/close');

    // 调用方传入的 notify_url 优先保留
    expect($result->getPayload()->get('notify_url'))->toEqual('https://pay.yansongda.cn/alipay/notify');
    expect($result->getPayload()->get('out_trade_no'))->toEqual('yansongda-2026');
});

it('notify_url 回退到配置', function () {
    $plugin = new ClosePlugin();
    // 调用方未传 notify_url 时回落租户配置（官方 AlipayTradeCloseModel 独有 notify_url 字段）
    Pay::config([
        'alipay' => [
            'alipay-v3-close' => [
                'app_id' => 'alipay_v3_test_app_id',
                'app_secret_cert' => cert_path('alipay-v3/app_secret_test.pem'),

                'app_public_cert_path' => cert_path('alipay-v3/alipay_public_cert_test.crt'),
                'alipay_public_cert_path' => cert_path('alipay-v3/alipay_public_cert_test.crt'),
                'notify_url' => 'https://pay.yansongda.cn/alipay/close-notify',
            ],
        ],
        '_force' => true,
    ]);

    $params = ['_config' => 'alipay-v3-close', 'out_trade_no' => 'yansongda-2026'];
    $rocket = (new Rocket())->setParams($params)->setPayload(new Collection($params));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->get('notify_url'))->toEqual('https://pay.yansongda.cn/alipay/close-notify');
});

it('缺少 notify_url', function () {
    $plugin = new ClosePlugin();
    // 调用方未传且租户未配置：notify_url 为 null（alipay-v3 测试租户无 notify_url 配置）
    $params = ['_config' => 'alipay-v3', 'out_trade_no' => 'yansongda-2026'];
    $rocket = (new Rocket())->setParams($params)->setPayload(new Collection($params));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->get('notify_url'))->toBeNull();
});

