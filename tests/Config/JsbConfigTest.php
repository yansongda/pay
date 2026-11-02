<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\JsbConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;

it('construct valid config', function () {
    $validConfig = [
        'partner_id' => 'partner_123',
        'public_key_code' => '00',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'jsb_public_cert_path' => '/path/to/jsb.pem',
    ];
    $config = new JsbConfig($validConfig);

    expect($config->getTenant())->toBe('default');
    expect($config->getPartnerId())->toBe('partner_123');
    expect($config->getPublicKeyCode())->toBe('00');
    expect($config->getMchSecretCertPath())->toBe('/path/to/secret.pem');
    expect($config->getJsbPublicCertPath())->toBe('/path/to/jsb.pem');
    expect($config->getMode())->toBe(PayMode::NORMAL);
});

it('construct with tenant', function () {
    $validConfig = [
        'partner_id' => 'partner_123',
        'public_key_code' => '00',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'jsb_public_cert_path' => '/path/to/jsb.pem',
    ];
    $config = new JsbConfig($validConfig, 'custom_tenant');

    expect($config->getTenant())->toBe('custom_tenant');
});

it('construct missing required', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionMessage('配置异常: 缺少江苏银行配置 -- [partner_id]');

    $config = new JsbConfig([
        // missing partner_id
        'public_key_code' => '00',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'jsb_public_cert_path' => '/path/to/jsb.pem',
    ]);
    $config->validate();
});

it('optional getters', function () {
    $validConfig = [
        'partner_id' => 'partner_123',
        'public_key_code' => '00',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'jsb_public_cert_path' => '/path/to/jsb.pem',
    ];
    $config = new JsbConfig(array_merge($validConfig, [
        'svr_code' => 'svr_123',
        'mch_public_cert_path' => '/path/to/public.pem',
        'notify_url' => 'https://notify.com',
    ]));

    expect($config->getSvrCode())->toBe('svr_123');
    expect($config->getMchPublicCertPath())->toBe('/path/to/public.pem');
    expect($config->getNotifyUrl())->toBe('https://notify.com');
});

it('optional getters null', function () {
    $validConfig = [
        'partner_id' => 'partner_123',
        'public_key_code' => '00',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'jsb_public_cert_path' => '/path/to/jsb.pem',
    ];
    $config = new JsbConfig($validConfig);

    expect($config->getSvrCode())->toBe('');
    expect($config->getMchPublicCertPath())->toBeNull();
    expect($config->getNotifyUrl())->toBeNull();
});

it('mode sandbox', function () {
    $validConfig = [
        'partner_id' => 'partner_123',
        'public_key_code' => '00',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'jsb_public_cert_path' => '/path/to/jsb.pem',
    ];
    $config = new JsbConfig(array_merge($validConfig, [
        // 兼容历史 int 写法：0/1/2
        'mode' => 1,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('mode with pay mode enum', function () {
    $validConfig = [
        'partner_id' => 'partner_123',
        'public_key_code' => '00',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'jsb_public_cert_path' => '/path/to/jsb.pem',
    ];
    $config = new JsbConfig(array_merge($validConfig, [
        'mode' => PayMode::SANDBOX,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('service mode throws exception', function () {
    $validConfig = [
        'partner_id' => 'partner_123',
        'public_key_code' => '00',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'jsb_public_cert_path' => '/path/to/jsb.pem',
    ];
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_PROVIDER_INVALID);
    $this->expectExceptionMessage('配置异常: [mode] 配置不合法');

    $config = new JsbConfig(array_merge($validConfig, [
        'mode' => PayMode::SERVICE,
    ]));
    $config->validate();
});

