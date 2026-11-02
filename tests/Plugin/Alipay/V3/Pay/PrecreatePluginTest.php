<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\PrecreatePlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new PrecreatePlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, '参数异常: 扫码支付（Precreate），参数为空', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new PrecreatePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'alipay-v3'])->setPayload(new Collection([
        'out_trade_no' => '2023122621450002',
        'total_amount' => '0.01',
        'subject' => 'yansongda 测试 - V3 Scan',
    ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->all())->toEqual([
        'out_trade_no' => '2023122621450002',
        'total_amount' => '0.01',
        'subject' => 'yansongda 测试 - V3 Scan',
        '_method' => 'POST',
        '_url' => '/v3/alipay/trade/precreate',
        'notify_url' => null,
    ]);
});

it('使用配置中的 notify_url', function () {
    $plugin = new PrecreatePlugin();
    // 注册带 notify_url 的临时 v3 租户（公钥模式）
    Pay::config([
        'alipay' => [
            'alipay-v3-notify' => [
                'app_id' => 'alipay_v3_test_app_id',
                'app_secret_cert' => cert_path('alipay-v3/app_secret_test.pem'),

                'app_public_cert_path' => cert_path('alipay-v3/alipay_public_cert_test.crt'),
                'alipay_public_cert_path' => cert_path('alipay-v3/alipay_public_cert_test.crt'),
                'notify_url' => 'https://pay.yansongda.cn/v3/notify',
            ],
        ],
        '_force' => true,
    ]);

    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'alipay-v3-notify'])->setPayload(new Collection([
        'out_trade_no' => '2023122621450002',
        'total_amount' => '0.01',
        'subject' => 'yansongda 测试 - V3 Scan',
    ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->get('_method'))->toBe('POST');
    expect($result->getPayload()->get('_url'))->toBe('/v3/alipay/trade/precreate');
    expect($result->getPayload()->get('notify_url'))->toBe('https://pay.yansongda.cn/v3/notify');
});

it('参数覆盖 notify_url', function () {
    $plugin = new PrecreatePlugin();
    // 注册带 notify_url 的临时 v3 租户（公钥模式）
    Pay::config([
        'alipay' => [
            'alipay-v3-notify' => [
                'app_id' => 'alipay_v3_test_app_id',
                'app_secret_cert' => cert_path('alipay-v3/app_secret_test.pem'),

                'app_public_cert_path' => cert_path('alipay-v3/alipay_public_cert_test.crt'),
                'alipay_public_cert_path' => cert_path('alipay-v3/alipay_public_cert_test.crt'),
                'notify_url' => 'https://pay.yansongda.cn/v3/notify',
            ],
        ],
        '_force' => true,
    ]);

    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'alipay-v3-notify'])->setPayload(new Collection([
        'out_trade_no' => '2023122621450002',
        'total_amount' => '0.01',
        'subject' => 'yansongda 测试 - V3 Scan',
        'notify_url' => 'https://pay.yansongda.cn/override',
    ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->get('notify_url'))->toBe('https://pay.yansongda.cn/override');
});

