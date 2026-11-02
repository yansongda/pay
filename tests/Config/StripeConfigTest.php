<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\StripeConfig;
use Yansongda\Pay\Enum\PayMode;

it('构造合法配置', function () {
    $validConfig = [
        'secret_key' => 'sk_test_123',
    ];
    $config = new StripeConfig($validConfig);

    expect($config->getTenant())->toBe('default');
    expect($config->getSecretKey())->toBe('sk_test_123');
    expect($config->getMode())->toBe(PayMode::NORMAL);
});

it('指定租户构造', function () {
    $validConfig = [
        'secret_key' => 'sk_test_123',
    ];
    $config = new StripeConfig($validConfig, 'custom_tenant');

    expect($config->getTenant())->toBe('custom_tenant');
});

it('缺少必填项构造', function () {

    $config = new StripeConfig([]);
    $config->validate();
})->throws(InvalidConfigException::class, '配置异常: 缺少 Stripe 配置 -- [secret_key]');

it('可选 getter', function () {
    $validConfig = [
        'secret_key' => 'sk_test_123',
    ];
    $config = new StripeConfig(array_merge($validConfig, [
        'webhook_secret' => 'whsec_123',
        'notify_url' => 'https://notify.com',
        'success_url' => 'https://success.com',
        'cancel_url' => 'https://cancel.com',
    ]));

    expect($config->getWebhookSecret())->toBe('whsec_123');
    expect($config->getNotifyUrl())->toBe('https://notify.com');
    expect($config->getSuccessUrl())->toBe('https://success.com');
    expect($config->getCancelUrl())->toBe('https://cancel.com');
});

it('可选 getter 返回 null', function () {
    $validConfig = [
        'secret_key' => 'sk_test_123',
    ];
    $config = new StripeConfig($validConfig);

    expect($config->getWebhookSecret())->toBeNull();
    expect($config->getNotifyUrl())->toBeNull();
    expect($config->getSuccessUrl())->toBeNull();
    expect($config->getCancelUrl())->toBeNull();
});

it('沙箱模式', function () {
    $validConfig = [
        'secret_key' => 'sk_test_123',
    ];
    $config = new StripeConfig(array_merge($validConfig, [
        // 兼容历史 int 写法：0/1/2
        'mode' => 1,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('使用 PayMode 枚举的模式', function () {
    $validConfig = [
        'secret_key' => 'sk_test_123',
    ];
    $config = new StripeConfig(array_merge($validConfig, [
        'mode' => PayMode::SANDBOX,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

