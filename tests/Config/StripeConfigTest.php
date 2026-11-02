<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\StripeConfig;
use Yansongda\Pay\Enum\PayMode;

it('construct valid config', function () {
    $validConfig = [
        'secret_key' => 'sk_test_123',
    ];
    $config = new StripeConfig($validConfig);

    expect($config->getTenant())->toBe('default');
    expect($config->getSecretKey())->toBe('sk_test_123');
    expect($config->getMode())->toBe(PayMode::NORMAL);
});

it('construct with tenant', function () {
    $validConfig = [
        'secret_key' => 'sk_test_123',
    ];
    $config = new StripeConfig($validConfig, 'custom_tenant');

    expect($config->getTenant())->toBe('custom_tenant');
});

it('construct missing required', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionMessage('配置异常: 缺少 Stripe 配置 -- [secret_key]');

    $config = new StripeConfig([]);
    $config->validate();
});

it('optional getters', function () {
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

it('optional getters null', function () {
    $validConfig = [
        'secret_key' => 'sk_test_123',
    ];
    $config = new StripeConfig($validConfig);

    expect($config->getWebhookSecret())->toBeNull();
    expect($config->getNotifyUrl())->toBeNull();
    expect($config->getSuccessUrl())->toBeNull();
    expect($config->getCancelUrl())->toBeNull();
});

it('mode sandbox', function () {
    $validConfig = [
        'secret_key' => 'sk_test_123',
    ];
    $config = new StripeConfig(array_merge($validConfig, [
        // 兼容历史 int 写法：0/1/2
        'mode' => 1,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('mode with pay mode enum', function () {
    $validConfig = [
        'secret_key' => 'sk_test_123',
    ];
    $config = new StripeConfig(array_merge($validConfig, [
        'mode' => PayMode::SANDBOX,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

