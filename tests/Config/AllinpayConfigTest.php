<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AllinpayConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;

it('构造合法配置', function () {
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

it('指定租户构造', function () {
    $validConfig = [
        'cusid' => '9900000',
        'appid' => '000000',
        'mch_secret_key' => '/path/to/secret.pem',
        'allinpay_public_key' => '/path/to/allinpay.pem',
    ];
    $config = new AllinpayConfig($validConfig, 'custom_tenant');

    expect($config->getTenant())->toBe('custom_tenant');
});

it('缺少必填项构造', function () {

    $config = new AllinpayConfig([
        'appid' => '000000',
        'mch_secret_key' => '/path/to/secret.pem',
        'allinpay_public_key' => '/path/to/allinpay.pem',
    ]);
    $config->validate();
})->throws(InvalidConfigException::class, null, Exception::CONFIG_ALLINPAY_INVALID);

it('可选 getter', function () {
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

it('使用 PayMode 枚举的模式', function () {
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

it('服务商模式抛出异常', function () {
    $validConfig = [
        'cusid' => '9900000',
        'appid' => '000000',
        'mch_secret_key' => '/path/to/secret.pem',
        'allinpay_public_key' => '/path/to/allinpay.pem',
    ];

    $config = new AllinpayConfig(array_merge($validConfig, [
        'mode' => PayMode::SERVICE,
    ]));
    $config->validate();
})->throws(InvalidConfigException::class, null, Exception::CONFIG_PROVIDER_INVALID);

