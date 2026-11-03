<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Enum\PayMode;

describe('构造', function () {
    it('合法配置', function () {
        $validConfig = [
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => cert_path('alipayAppPublicCert.crt'),
            'alipay_public_cert_path' => cert_path('alipayPublicCert.crt'),
            'alipay_root_cert_path' => cert_path('alipayRootCert.crt'),
        ];
        $config = new AlipayConfig($validConfig);

        expect($config->getTenant())->toBe('default');
        expect($config->getAppId())->toBe('test_app_id');
        expect($config->getAppSecretCert())->toBe('test_secret');
        expect($config->getMode())->toBe(PayMode::NORMAL);
    });

    it('指定租户', function () {
        $validConfig = [
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => cert_path('alipayAppPublicCert.crt'),
            'alipay_public_cert_path' => cert_path('alipayPublicCert.crt'),
            'alipay_root_cert_path' => cert_path('alipayRootCert.crt'),
        ];
        $config = new AlipayConfig($validConfig, 'custom_tenant');

        expect($config->getTenant())->toBe('custom_tenant');
    });

    it('缺少必填项', function () {

        $config = new AlipayConfig([
            // missing app_id
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => cert_path('alipayAppPublicCert.crt'),
            'alipay_public_cert_path' => cert_path('alipayPublicCert.crt'),
            'alipay_root_cert_path' => cert_path('alipayRootCert.crt'),
        ]);
        $config->validate();
    })->throws(InvalidConfigException::class, '配置异常: 缺少支付宝配置 -- [app_id]');

    it('缺少应用公钥证书路径', function () {

        $config = new AlipayConfig([
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'alipay_public_cert_path' => cert_path('alipayPublicCert.crt'),
        ]);
        $config->validate();
    })->throws(InvalidConfigException::class, '配置异常: 缺少支付宝配置 -- [app_public_cert_path]');
});

describe('getter', function () {
    it('可选', function () {
        $validConfig = [
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => cert_path('alipayAppPublicCert.crt'),
            'alipay_public_cert_path' => cert_path('alipayPublicCert.crt'),
            'alipay_root_cert_path' => cert_path('alipayRootCert.crt'),
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

    it('可选返回 null', function () {
        $validConfig = [
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => cert_path('alipayAppPublicCert.crt'),
            'alipay_public_cert_path' => cert_path('alipayPublicCert.crt'),
            'alipay_root_cert_path' => cert_path('alipayRootCert.crt'),
        ];
        $config = new AlipayConfig($validConfig);

        expect($config->getNotifyUrl())->toBeNull();
        expect($config->getReturnUrl())->toBeNull();
        expect($config->getAppAuthToken())->toBeNull();
        expect($config->getServiceProviderId())->toBeNull();
    });

    it('证书', function () {
        $validConfig = [
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => cert_path('alipayAppPublicCert.crt'),
            'alipay_public_cert_path' => cert_path('alipayPublicCert.crt'),
            'alipay_root_cert_path' => cert_path('alipayRootCert.crt'),
        ];
        $config = new AlipayConfig($validConfig);

        expect($config->getAppPublicCertPath())->toBe(cert_path('alipayAppPublicCert.crt'));
        expect($config->getAlipayPublicCertPath())->toBe(cert_path('alipayPublicCert.crt'));
        expect($config->getAlipayRootCertPath())->toBe(cert_path('alipayRootCert.crt'));
    });
});

describe('toArray', function () {
    it('保留向后兼容的蛇形键', function () {
        $validConfig = [
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => cert_path('alipayAppPublicCert.crt'),
            'alipay_public_cert_path' => cert_path('alipayPublicCert.crt'),
            'alipay_root_cert_path' => cert_path('alipayRootCert.crt'),
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
            'app_public_cert_path' => cert_path('alipayAppPublicCert.crt'),
            'alipay_public_cert_path' => cert_path('alipayPublicCert.crt'),
            'alipay_root_cert_path' => cert_path('alipayRootCert.crt'),
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
});

describe('沙箱', function () {
    it('模式', function () {
        $validConfig = [
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => cert_path('alipayAppPublicCert.crt'),
            'alipay_public_cert_path' => cert_path('alipayPublicCert.crt'),
            'alipay_root_cert_path' => cert_path('alipayRootCert.crt'),
        ];
        $config = new AlipayConfig(array_merge($validConfig, [
            // 兼容历史 int 写法：0/1/2
            'mode' => 1,
        ]));

        expect($config->getMode())->toBe(PayMode::SANDBOX);
    });

    it('模式（枚举）', function () {
        $validConfig = [
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => cert_path('alipayAppPublicCert.crt'),
            'alipay_public_cert_path' => cert_path('alipayPublicCert.crt'),
            'alipay_root_cert_path' => cert_path('alipayRootCert.crt'),
        ];
        $config = new AlipayConfig(array_merge($validConfig, [
            'mode' => PayMode::SANDBOX,
        ]));

        expect($config->getMode())->toBe(PayMode::SANDBOX);
    });
});

describe('配置分发', function () {
    it('正常', function () {
        $validConfig = [
            'app_id' => 'test_app_id',
            'app_secret_cert' => 'test_secret',
            'app_public_cert_path' => cert_path('alipayAppPublicCert.crt'),
            'alipay_public_cert_path' => cert_path('alipayPublicCert.crt'),
            'alipay_root_cert_path' => cert_path('alipayRootCert.crt'),
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
});