<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AirwallexConfig;
use Yansongda\Pay\Enum\PayMode;

it('construct valid config', function () {
    $validConfig = [
        'client_id' => 'airwallex_client_id',
        'api_key' => 'airwallex_api_key',
    ];
    $config = new AirwallexConfig($validConfig);

    expect($config->getTenant())->toBe('default');
    expect($config->getClientId())->toBe('airwallex_client_id');
    expect($config->getApiKey())->toBe('airwallex_api_key');
    expect($config->getMode())->toBe(PayMode::NORMAL);
});

it('construct with tenant', function () {
    $validConfig = [
        'client_id' => 'airwallex_client_id',
        'api_key' => 'airwallex_api_key',
    ];
    $config = new AirwallexConfig($validConfig, 'secondary');

    expect($config->getTenant())->toBe('secondary');
});

it('construct missing client id', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionMessage('配置异常: 缺少 Airwallex 配置 -- [client_id]');

    $config = new AirwallexConfig(['api_key' => 'airwallex_api_key']);
    $config->validate();
});

it('construct missing api key', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionMessage('配置异常: 缺少 Airwallex 配置 -- [api_key]');

    $config = new AirwallexConfig(['client_id' => 'airwallex_client_id']);
    $config->validate();
});

it('optional getters', function () {
    $validConfig = [
        'client_id' => 'airwallex_client_id',
        'api_key' => 'airwallex_api_key',
    ];
    $config = new AirwallexConfig(array_merge($validConfig, [
        'webhook_secret' => 'airwallex_webhook_secret',
        'return_url' => 'https://pay.yansongda.cn/airwallex/return',
        'api_version' => '2024-06-14',
        'on_behalf_of' => 'acct_123',
        'mode' => PayMode::SANDBOX,
    ]));

    expect($config->getWebhookSecret())->toBe('airwallex_webhook_secret');
    expect($config->getReturnUrl())->toBe('https://pay.yansongda.cn/airwallex/return');
    expect($config->getApiVersion())->toBe('2024-06-14');
    expect($config->getOnBehalfOf())->toBe('acct_123');
    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('mode with pay mode enum', function () {
    $validConfig = [
        'client_id' => 'airwallex_client_id',
        'api_key' => 'airwallex_api_key',
    ];
    $config = new AirwallexConfig(array_merge($validConfig, [
        'mode' => PayMode::SANDBOX,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('access token cache fields', function () {
    $validConfig = [
        'client_id' => 'airwallex_client_id',
        'api_key' => 'airwallex_api_key',
    ];
    $config = new AirwallexConfig($validConfig);

    $config->setAccessToken('airwallex_access_token');
    $config->setAccessTokenExpiry('1893456000');

    expect($config->getAccessToken())->toBe('airwallex_access_token');
    expect($config->getAccessTokenExpiry())->toBe(1893456000);
});

it('nullable optional fields', function () {
    $validConfig = [
        'client_id' => 'airwallex_client_id',
        'api_key' => 'airwallex_api_key',
    ];
    $config = new AirwallexConfig(array_merge($validConfig, [
        'webhook_secret' => null,
        'return_url' => null,
        'api_version' => null,
        'on_behalf_of' => null,
        'access_token' => null,
        'access_token_expiry' => null,
    ]));

    expect($config->getWebhookSecret())->toBeNull();
    expect($config->getReturnUrl())->toBeNull();
    expect($config->getApiVersion())->toBeNull();
    expect($config->getOnBehalfOf())->toBeNull();
    expect($config->getAccessToken())->toBeNull();
    expect($config->getAccessTokenExpiry())->toBeNull();
});

