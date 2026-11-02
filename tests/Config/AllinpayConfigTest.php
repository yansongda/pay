<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AllinpayConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;

it('construct valid config', function () {
    $validConfig = [
        'cusid' => '9900000',
        'appid' => '000000',
        'mch_secret_key' => '/path/to/secret.pem',
        'allinpay_public_key' => '/path/to/allinpay.pem',
    ];
    $config = new AllinpayConfig($validConfig);

    expect($config->getTenant())->toBe('default');
    expect($config->getCusid())->toBe('9900000');
    expect($config->getAppid())->toBe('000000');
    expect($config->getOrgid())->toBeNull();
    expect($config->getMchSecretKey())->toBe('/path/to/secret.pem');
    expect($config->getAllinpayPublicKey())->toBe('/path/to/allinpay.pem');
    expect($config->getMode())->toBe(PayMode::NORMAL);
});

it('construct with tenant', function () {
    $validConfig = [
        'cusid' => '9900000',
        'appid' => '000000',
        'mch_secret_key' => '/path/to/secret.pem',
        'allinpay_public_key' => '/path/to/allinpay.pem',
    ];
    $config = new AllinpayConfig($validConfig, 'custom_tenant');

    expect($config->getTenant())->toBe('custom_tenant');
});

it('construct missing required', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_ALLINPAY_INVALID);

    $config = new AllinpayConfig([
        'appid' => '000000',
        'mch_secret_key' => '/path/to/secret.pem',
        'allinpay_public_key' => '/path/to/allinpay.pem',
    ]);
    $config->validate();
});

it('optional getters', function () {
    $validConfig = [
        'cusid' => '9900000',
        'appid' => '000000',
        'mch_secret_key' => '/path/to/secret.pem',
        'allinpay_public_key' => '/path/to/allinpay.pem',
    ];
    $config = new AllinpayConfig(array_merge($validConfig, [
        'orgid' => '999999',
        'notify_url' => 'https://notify.com',
        'mode' => PayMode::SANDBOX,
    ]));

    expect($config->getOrgid())->toBe('999999');
    expect($config->getNotifyUrl())->toBe('https://notify.com');
    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('mode with pay mode enum', function () {
    $validConfig = [
        'cusid' => '9900000',
        'appid' => '000000',
        'mch_secret_key' => '/path/to/secret.pem',
        'allinpay_public_key' => '/path/to/allinpay.pem',
    ];
    $config = new AllinpayConfig(array_merge($validConfig, [
        'mode' => PayMode::SANDBOX,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('service mode throws exception', function () {
    $validConfig = [
        'cusid' => '9900000',
        'appid' => '000000',
        'mch_secret_key' => '/path/to/secret.pem',
        'allinpay_public_key' => '/path/to/allinpay.pem',
    ];
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_PROVIDER_INVALID);

    $config = new AllinpayConfig(array_merge($validConfig, [
        'mode' => PayMode::SERVICE,
    ]));
    $config->validate();
});

