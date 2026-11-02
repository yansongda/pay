<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;

it('construct valid config', function () {
    $validConfig = [
        'app_id' => 'tt123456',
        'app_secret' => 'test_app_secret',
        'app_private_key' => 'test_app_private_key',
        'douyin_public_key' => 'test_douyin_public_key',
    ];
    $config = new DouyinConfig($validConfig);

    expect($config->getTenant())->toBe('default');
    expect($config->getAppId())->toBe('tt123456');
    expect($config->getAppSecret())->toBe('test_app_secret');
    expect($config->getAppPrivateKey())->toBe('test_app_private_key');
    expect($config->getDouyinPublicKey())->toBe('test_douyin_public_key');
    expect($config->getMode())->toBe(PayMode::NORMAL);
});

it('construct with tenant', function () {
    $validConfig = [
        'app_id' => 'tt123456',
        'app_secret' => 'test_app_secret',
        'app_private_key' => 'test_app_private_key',
        'douyin_public_key' => 'test_douyin_public_key',
    ];
    $config = new DouyinConfig($validConfig, 'custom_tenant');

    expect($config->getTenant())->toBe('custom_tenant');
});

it('construct missing app id', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_DOUYIN_INVALID);
    $this->expectExceptionMessage('配置异常: 缺少抖音配置 -- [app_id]');

    $config = new DouyinConfig([
        // missing app_id
        'app_secret' => 'test_app_secret',
    ]);
    $config->validate();
});

it('construct missing app secret', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_DOUYIN_INVALID);
    $this->expectExceptionMessage('配置异常: 缺少抖音配置 -- [app_secret]');

    $config = new DouyinConfig([
        'app_id' => 'tt123456',
        // missing app_secret
    ]);
    $config->validate();
});

it('optional getters', function () {
    $validConfig = [
        'app_id' => 'tt123456',
        'app_secret' => 'test_app_secret',
        'app_private_key' => 'test_app_private_key',
        'douyin_public_key' => 'test_douyin_public_key',
    ];
    $config = new DouyinConfig(array_merge($validConfig, [
        'notify_url' => 'https://notify.com',
    ]));

    expect($config->getNotifyUrl())->toBe('https://notify.com');
});

it('optional getters null', function () {
    $validConfig = [
        'app_id' => 'tt123456',
        'app_secret' => 'test_app_secret',
        'app_private_key' => 'test_app_private_key',
        'douyin_public_key' => 'test_douyin_public_key',
    ];
    $config = new DouyinConfig($validConfig);

    expect($config->getNotifyUrl())->toBeNull();
});

it('mode sandbox', function () {
    $validConfig = [
        'app_id' => 'tt123456',
        'app_secret' => 'test_app_secret',
        'app_private_key' => 'test_app_private_key',
        'douyin_public_key' => 'test_douyin_public_key',
    ];
    $config = new DouyinConfig(array_merge($validConfig, [
        // 兼容历史 int 写法：0/1/2
        'mode' => 1,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('mode with pay mode enum', function () {
    $validConfig = [
        'app_id' => 'tt123456',
        'app_secret' => 'test_app_secret',
        'app_private_key' => 'test_app_private_key',
        'douyin_public_key' => 'test_douyin_public_key',
    ];
    $config = new DouyinConfig(array_merge($validConfig, [
        'mode' => PayMode::SANDBOX,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('invalid mode throws exception', function () {
    $validConfig = [
        'app_id' => 'tt123456',
        'app_secret' => 'test_app_secret',
        'app_private_key' => 'test_app_private_key',
        'douyin_public_key' => 'test_douyin_public_key',
    ];
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_PROVIDER_INVALID);
    $this->expectExceptionMessage('配置异常: [mode] 配置不合法');

    $config = new DouyinConfig(array_merge($validConfig, [
        'mode' => 99999,
    ]));
    $config->validate();
});

