<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;

it('construct with array', function () {
    $config = new Config([
        'wechat' => [
            'default' => [
                'app_id' => 'test_app_id',
                'mch_id' => 'test_mch_id',
                'mch_secret_key' => '12345678901234567890123456789012',
                'mch_secret_cert' => 'test_cert',
                'mch_public_cert_path' => 'test_path',
                'notify_url' => 'https://test.com',
            ],
        ],
    ]);

    $wechatConfig = $config->get('wechat.default');
    expect($wechatConfig)->toBeInstanceOf(WechatConfig::class);
    expect($wechatConfig->getMchId())->toBe('test_mch_id');
});

it('get provider config', function () {
    $config = new Config([
        'alipay' => [
            'default' => [
                'app_id' => 'test_app_id',
                'app_secret_cert' => 'test_secret',
                'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
                'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
                'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
            ],
        ],
    ]);

    /** @var AlipayConfig $alipayConfig */
    $alipayConfig = $config->getProviderConfig('alipay');
    expect($alipayConfig)->toBeInstanceOf(AlipayConfig::class);
    expect($alipayConfig->getAppId())->toBe('test_app_id');
});

it('get provider config with tenant', function () {
    $config = new Config([
        'alipay' => [
            'tenant1' => [
                'app_id' => 'tenant1_app_id',
                'app_secret_cert' => 'test_secret',
                'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
                'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
                'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
            ],
        ],
    ]);

    /** @var AlipayConfig $alipayConfig */
    $alipayConfig = $config->getProviderConfig('alipay', 'tenant1');
    expect($alipayConfig->getAppId())->toBe('tenant1_app_id');
});

it('get provider config unknown provider', function () {
    $config = new Config([]);

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_PROVIDER_INVALID);
    $this->expectExceptionMessage('配置异常: 未知的 Provider - unknown');

    $config->getProviderConfig('unknown');
});

it('get provider config missing tenant', function () {
    $config = new Config([
        'alipay' => [
            'default' => [
                'app_id' => 'test_app_id',
                'app_secret_cert' => 'test_secret',
                'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
                'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
                'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
            ],
        ],
    ]);

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_PROVIDER_INVALID);
    $this->expectExceptionMessage('配置异常: alipay.missing_tenant 配置不存在');

    $config->getProviderConfig('alipay', 'missing_tenant');
});

it('config object not converted', function () {
    $existingConfig = new WechatConfig([
        'app_id' => 'existing_app_id',
        'mch_id' => 'existing_mch_id',
        'mch_secret_key' => '12345678901234567890123456789012',
        'mch_secret_cert' => 'existing_cert',
        'mch_public_cert_path' => 'existing_path',
        'notify_url' => 'https://existing.com',
    ], 'test_tenant');

    $config = new Config([
        'wechat' => [
            'test_tenant' => $existingConfig,
        ],
    ]);

    $wechatConfig = $config->getProviderConfig('wechat', 'test_tenant');
    expect($wechatConfig)->toBe($existingConfig);
});

it('provider config to array uses snake case external keys', function () {
    $config = new Config([
        'alipay' => [
            'default' => [
                'app_id' => 'test_app_id',
                'app_secret_cert' => 'test_secret',
                'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
                'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
                'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
            ],
        ],
    ]);

    /** @var AlipayConfig $alipayConfig */
    $alipayConfig = $config->getProviderConfig('alipay');
    expect($alipayConfig->toArray())->not->toHaveKey('appPublicCertSn');
    expect($alipayConfig->toArray())->not->toHaveKey('alipayRootCertSn');
});

it('construct with incomplete unused provider config', function () {
    $config = new Config([
        'wechat' => [
            'default' => [
                'app_id' => 'wx123',
                'mch_id' => 'test_mch_id',
                'mch_secret_key' => '12345678901234567890123456789012',
                'mch_secret_cert' => 'test_cert',
                'mch_public_cert_path' => 'test_path',
                'notify_url' => 'https://test.com',
            ],
        ],
        'alipay' => [
            'default' => [],
        ],
    ]);

    // 构造不抛异常，且微信配置正常可用
    $wechatConfig = $config->get('wechat.default');
    expect($wechatConfig)->toBeInstanceOf(WechatConfig::class);

    // 访问不完整的支付宝配置时才抛异常
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionMessage('配置异常: 缺少支付宝配置 -- [app_id]');

    $config->getProviderConfig('alipay');
});

