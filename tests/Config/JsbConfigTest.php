<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\JsbConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;

it('构造合法配置', function () {
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

it('指定租户构造', function () {
    $validConfig = [
        'partner_id' => 'partner_123',
        'public_key_code' => '00',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'jsb_public_cert_path' => '/path/to/jsb.pem',
    ];
    $config = new JsbConfig($validConfig, 'custom_tenant');

    expect($config->getTenant())->toBe('custom_tenant');
});

it('缺少必填项构造', function () {

    $config = new JsbConfig([
        // missing partner_id
        'public_key_code' => '00',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'jsb_public_cert_path' => '/path/to/jsb.pem',
    ]);
    $config->validate();
})->throws(InvalidConfigException::class, '配置异常: 缺少江苏银行配置 -- [partner_id]');

it('可选 getter', function () {
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

it('可选 getter 返回 null', function () {
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

it('沙箱模式', function () {
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

it('使用 PayMode 枚举的模式', function () {
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

it('服务商模式抛出异常', function () {
    // 江苏银行无服务商模式，mode = SERVICE 应被校验拦截
    $validConfig = [
        'partner_id' => 'partner_123',
        'public_key_code' => '00',
        'mch_secret_cert_path' => '/path/to/secret.pem',
        'jsb_public_cert_path' => '/path/to/jsb.pem',
    ];

    $config = new JsbConfig(array_merge($validConfig, [
        'mode' => PayMode::SERVICE,
    ]));
    $config->validate();
})->throws(InvalidConfigException::class, '配置异常: [mode] 配置不合法', Exception::CONFIG_PROVIDER_INVALID);

