<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Enum\PayMode;

it('构造合法配置', function () {
    $validConfig = [
        'app_id' => 'test_app_id',
        'app_secret_cert' => 'test_secret',
        'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
        'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
        'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
    ];
    $config = new AlipayConfig($validConfig);

    expect($config->getTenant())->toBe('default');
    expect($config->getAppId())->toBe('test_app_id');
    expect($config->getAppSecretCert())->toBe('test_secret');
    expect($config->getMode())->toBe(PayMode::NORMAL);
});

it('指定租户构造', function () {
    $validConfig = [
        'app_id' => 'test_app_id',
        'app_secret_cert' => 'test_secret',
        'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
        'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
        'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
    ];
    $config = new AlipayConfig($validConfig, 'custom_tenant');

    expect($config->getTenant())->toBe('custom_tenant');
});

it('缺少必填项构造', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionMessage('配置异常: 缺少支付宝配置 -- [app_id]');

    $config = new AlipayConfig([
        // missing app_id
        'app_secret_cert' => 'test_secret',
        'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
        'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
        'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
    ]);
    $config->validate();
});

it('缺少应用公钥证书路径构造', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionMessage('配置异常: 缺少支付宝配置 -- [app_public_cert_path]');

    $config = new AlipayConfig([
        'app_id' => 'test_app_id',
        'app_secret_cert' => 'test_secret',
        'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
    ]);
    $config->validate();
});

it('可选 getter', function () {
    $validConfig = [
        'app_id' => 'test_app_id',
        'app_secret_cert' => 'test_secret',
        'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
        'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
        'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
    ];
    $config = new AlipayConfig(array_merge($validConfig, [
        'notify_url' => 'https://notify.com',
        'return_url' => 'https://return.com',
        'app_auth_token' => 'auth_token',
        'service_provider_id' => 'sp_id',
    ]));

    expect($config->getNotifyUrl())->toBe('https://notify.com');
    expect($config->getReturnUrl())->toBe('https://return.com');
    expect($config->getAppAuthToken())->toBe('auth_token');
    expect($config->getServiceProviderId())->toBe('sp_id');
});

it('可选 getter 返回 null', function () {
    $validConfig = [
        'app_id' => 'test_app_id',
        'app_secret_cert' => 'test_secret',
        'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
        'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
        'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
    ];
    $config = new AlipayConfig($validConfig);

    expect($config->getNotifyUrl())->toBeNull();
    expect($config->getReturnUrl())->toBeNull();
    expect($config->getAppAuthToken())->toBeNull();
    expect($config->getServiceProviderId())->toBeNull();
});

it('证书 getter', function () {
    $validConfig = [
        'app_id' => 'test_app_id',
        'app_secret_cert' => 'test_secret',
        'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
        'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
        'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
    ];
    $config = new AlipayConfig($validConfig);

    expect($config->getAppPublicCertPath())->toBe(__DIR__.'/../Cert/alipayAppPublicCert.crt');
    expect($config->getAlipayPublicCertPath())->toBe(__DIR__.'/../Cert/alipayPublicCert.crt');
    expect($config->getAlipayRootCertPath())->toBe(__DIR__.'/../Cert/alipayRootCert.crt');
});

it('转数组保留向后兼容的蛇形键', function () {
    $validConfig = [
        'app_id' => 'test_app_id',
        'app_secret_cert' => 'test_secret',
        'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
        'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
        'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
    ];
    $config = new AlipayConfig(array_merge($validConfig, [
        'notify_url' => 'https://notify.com',
        'return_url' => 'https://return.com',
        'app_auth_token' => 'auth_token',
        'service_provider_id' => 'sp_id',
    ]));

    $array = $config->toArray();
    ksort($array);

    $expected = [
        'app_id' => 'test_app_id',
        'app_secret_cert' => 'test_secret',
        'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
        'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
        'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
        'notify_url' => 'https://notify.com',
        'return_url' => 'https://return.com',
        'app_auth_token' => 'auth_token',
        'service_provider_id' => 'sp_id',
        'aes_key' => null,
        'mode' => PayMode::NORMAL,
        'tenant' => 'default',
    ];
    ksort($expected);

    expect($array)->toBe($expected);
});

it('沙箱模式', function () {
    $validConfig = [
        'app_id' => 'test_app_id',
        'app_secret_cert' => 'test_secret',
        'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
        'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
        'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
    ];
    $config = new AlipayConfig(array_merge($validConfig, [
        // 兼容历史 int 写法：0/1/2
        'mode' => 1,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('沙箱模式（枚举）', function () {
    $validConfig = [
        'app_id' => 'test_app_id',
        'app_secret_cert' => 'test_secret',
        'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
        'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
        'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
    ];
    $config = new AlipayConfig(array_merge($validConfig, [
        'mode' => PayMode::SANDBOX,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('配置分发', function () {
    $validConfig = [
        'app_id' => 'test_app_id',
        'app_secret_cert' => 'test_secret',
        'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
        'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
        'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
    ];
    $config = new \Yansongda\Pay\Config([
        'alipay' => [
            'default' => $validConfig,
        ],
    ]);

    $alipayConfig = $config->getProviderConfig('alipay');

    expect($alipayConfig)->toBeInstanceOf(AlipayConfig::class);
    expect($alipayConfig->getAppId())->toBe('test_app_id');
    expect($alipayConfig->getTenant())->toBe('default');
});

