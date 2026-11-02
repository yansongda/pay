<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\PaypalConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;

it('construct valid config', function () {
    $validConfig = [
        'client_id' => 'test_client_id',
        'app_secret' => 'test_app_secret',
    ];
    $config = new PaypalConfig($validConfig);

    expect($config->getTenant())->toBe('default');
    expect($config->getClientId())->toBe('test_client_id');
    expect($config->getAppSecret())->toBe('test_app_secret');
    expect($config->getMode())->toBe(PayMode::NORMAL);
});

it('construct with tenant', function () {
    $validConfig = [
        'client_id' => 'test_client_id',
        'app_secret' => 'test_app_secret',
    ];
    $config = new PaypalConfig($validConfig, 'custom_tenant');

    expect($config->getTenant())->toBe('custom_tenant');
});

it('construct missing required', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionMessage('配置异常: 缺少 PayPal 配置 -- [client_id]');

    $config = new PaypalConfig([
        // missing client_id
        'app_secret' => 'test_app_secret',
    ]);
    $config->validate();
});

it('optional getters', function () {
    $validConfig = [
        'client_id' => 'test_client_id',
        'app_secret' => 'test_app_secret',
    ];
    $config = new PaypalConfig(array_merge($validConfig, [
        'webhook_id' => 'webhook_123',
        'notify_url' => 'https://notify.com',
        'return_url' => 'https://return.com',
        'cancel_url' => 'https://cancel.com',
        'brand_name' => 'Test Brand',
    ]));

    expect($config->getWebhookId())->toBe('webhook_123');
    expect($config->getNotifyUrl())->toBe('https://notify.com');
    expect($config->getReturnUrl())->toBe('https://return.com');
    expect($config->getCancelUrl())->toBe('https://cancel.com');
    expect($config->getBrandName())->toBe('Test Brand');
});

it('optional getters null', function () {
    $validConfig = [
        'client_id' => 'test_client_id',
        'app_secret' => 'test_app_secret',
    ];
    $config = new PaypalConfig($validConfig);

    expect($config->getWebhookId())->toBeNull();
    expect($config->getNotifyUrl())->toBeNull();
    expect($config->getReturnUrl())->toBeNull();
    expect($config->getCancelUrl())->toBeNull();
    expect($config->getBrandName())->toBeNull();
    expect($config->getAccessToken())->toBeNull();
    expect($config->getAccessTokenExpiry())->toBeNull();
});

it('access token', function () {
    $validConfig = [
        'client_id' => 'test_client_id',
        'app_secret' => 'test_app_secret',
    ];
    $config = new PaypalConfig(array_merge($validConfig, [
        '_access_token' => 'token_abc',
        '_access_token_expiry' => 1234567890,
    ]));

    expect($config->getAccessToken())->toBe('token_abc');
    expect($config->getAccessTokenExpiry())->toBe(1234567890);
});

it('mode sandbox', function () {
    $validConfig = [
        'client_id' => 'test_client_id',
        'app_secret' => 'test_app_secret',
    ];
    $config = new PaypalConfig(array_merge($validConfig, [
        // 兼容历史 int 写法：0/1/2
        'mode' => 1,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('mode with pay mode enum', function () {
    $validConfig = [
        'client_id' => 'test_client_id',
        'app_secret' => 'test_app_secret',
    ];
    $config = new PaypalConfig(array_merge($validConfig, [
        'mode' => PayMode::SANDBOX,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('invalid mode throws exception', function () {
    $validConfig = [
        'client_id' => 'test_client_id',
        'app_secret' => 'test_app_secret',
    ];
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_PROVIDER_INVALID);
    $this->expectExceptionMessage('配置异常: [mode] 配置不合法');

    $config = new PaypalConfig(array_merge($validConfig, [
        'mode' => 99999,
    ]));
    $config->validate();
});

