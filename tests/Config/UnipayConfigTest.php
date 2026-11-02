<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Enum\PayMode;

it('构造合法配置', function () {
    $validConfig = [
        'mch_cert_path' => cert_path('unipayAppPublicCert.crt'),
        'mch_cert_password' => 'test_password',
    ];
    $config = new UnipayConfig($validConfig);

    expect($config->getTenant())->toBe('default');
    expect($config->getMode())->toBe(PayMode::NORMAL);
});

it('指定租户构造', function () {
    $validConfig = [
        'mch_cert_path' => cert_path('unipayAppPublicCert.crt'),
        'mch_cert_password' => 'test_password',
    ];
    $config = new UnipayConfig($validConfig, 'custom_tenant');

    expect($config->getTenant())->toBe('custom_tenant');
});

it('缺少必填项构造', function () {

    $config = new UnipayConfig([
        // missing mch_cert_path
        'mch_cert_password' => 'test_password',
    ]);
    $config->validate();
})->throws(InvalidConfigException::class, '配置异常: 缺少银联配置 -- [mch_cert_path]');

it('可选 getter', function () {
    $validConfig = [
        'mch_cert_path' => cert_path('unipayAppPublicCert.crt'),
        'mch_cert_password' => 'test_password',
    ];
    $config = new UnipayConfig(array_merge($validConfig, [
        'mch_id' => 'test_mch_id',
        'mch_secret_key' => 'secret_key',
        'notify_url' => 'https://notify.com',
        'return_url' => 'https://return.com',
        'unipay_public_cert_path' => '/path/to/cert',
    ]));

    expect($config->getMchId())->toBe('test_mch_id');
    expect($config->getMchSecretKey())->toBe('secret_key');
    expect($config->getNotifyUrl())->toBe('https://notify.com');
    expect($config->getReturnUrl())->toBe('https://return.com');
    expect($config->getUnipayPublicCertPath())->toBe('/path/to/cert');
});

it('可选 getter 返回 null', function () {
    $validConfig = [
        'mch_cert_path' => cert_path('unipayAppPublicCert.crt'),
        'mch_cert_password' => 'test_password',
    ];
    $config = new UnipayConfig($validConfig);

    expect($config->getMchId())->toBeNull();
    expect($config->getMchSecretKey())->toBeNull();
    expect($config->getNotifyUrl())->toBeNull();
    expect($config->getReturnUrl())->toBeNull();
    expect($config->getUnipayPublicCertPath())->toBeNull();
});

it('沙箱模式', function () {
    $validConfig = [
        'mch_cert_path' => cert_path('unipayAppPublicCert.crt'),
        'mch_cert_password' => 'test_password',
    ];
    $config = new UnipayConfig(array_merge($validConfig, [
        // 兼容历史 int 写法：0/1/2
        'mode' => 1,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('使用 PayMode 枚举的模式', function () {
    $validConfig = [
        'mch_cert_path' => cert_path('unipayAppPublicCert.crt'),
        'mch_cert_password' => 'test_password',
    ];
    $config = new UnipayConfig(array_merge($validConfig, [
        'mode' => PayMode::SANDBOX,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

