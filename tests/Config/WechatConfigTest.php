<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Config\WechatConfigVirtualPay;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Enum\Wechat\AppType;
use Yansongda\Pay\Exception\Exception;

describe('构造', function () {
    it('合法配置', function () {
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
        $config = new WechatConfig($validConfig);

        expect($config->getTenant())->toBe('default');
        expect($config->getMchId())->toBe('test_mch_id');
        expect($config->getMchSecretKey())->toBe('12345678901234567890123456789012');
        expect($config->getMchSecretCert())->toBe('test_cert');
        expect($config->getMchPublicCertPath())->toBe('test_path');
        expect($config->getNotifyUrl())->toBe('https://test.com');
        expect($config->getMode())->toBe(PayMode::NORMAL);
    });

    it('指定租户', function () {
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
        $config = new WechatConfig($validConfig, 'custom_tenant');

        expect($config->getTenant())->toBe('custom_tenant');
    });

    it('沙箱模式', function () {
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
        $config = new WechatConfig(array_merge($validConfig, [
            'mode' => PayMode::SANDBOX,
        ]));

        expect($config->getMode())->toBe(PayMode::SANDBOX);
        expect($config->getMode()->isSandbox())->toBeTrue();

        $config->validate();
    });

    it('整数形式的模式', function () {
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
        // 兼容历史 int 写法：0/1/2
        $config = new WechatConfig(array_merge($validConfig, [
            'mode' => 1,
        ]));

        expect($config->getMode())->toBe(PayMode::SANDBOX);
    });

    it('不传 notify_url', function () {
        $config = new WechatConfig([
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
        ]);

        expect($config->getNotifyUrl())->toBe('');
    });
});

describe('校验', function () {
    it('缺少必填项', function () {

        $config = new WechatConfig([
            // missing mch_id
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ]);
        $config->validate();
    })->throws(InvalidConfigException::class, '配置异常: 缺少微信配置 -- [mch_id]');

    it('密钥长度非法', function () {

        $config = new WechatConfig([
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => 'short_key', // 9 bytes, not 32
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ]);
        $config->validate();
    })->throws(InvalidConfigException::class, '配置异常: mch_secret_key 长度应为 32 字节');

    it('服务商模式缺少子商户号', function () {

        $config = new WechatConfig([
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
            'mode' => PayMode::SERVICE,
            // missing sub_mch_id
        ]);
        $config->validate();
    })->throws(InvalidConfigException::class, '配置异常: 服务商模式下缺少 [sub_mch_id]');

    it('服务商模式带子商户号', function () {
        $config = new WechatConfig([
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
            'mode' => PayMode::SERVICE,
            'sub_mch_id' => 'sub_mch_id',
        ]);

        expect($config->getMode())->toBe(PayMode::SERVICE);
        expect($config->getSubMchId())->toBe('sub_mch_id');
    });

    it('非法模式抛出异常', function () {
        // 未覆盖 supportedModes 的 Provider 默认支持三种 mode，其余取值应被校验拦截
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];

        $config = new WechatConfig(array_merge($validConfig, [
            'mode' => 99999,
        ]));
        $config->validate();
    })->throws(InvalidConfigException::class, '配置异常: [mode] 配置不合法', Exception::CONFIG_PROVIDER_INVALID);
});

describe('getter', function () {
    it('按类型获取 app_id', function () {
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
        $config = new WechatConfig(array_merge($validConfig, [
            'mp_app_id' => 'mp_app_id',
            'mini_app_id' => 'mini_app_id',
            'app_id' => 'app_id',
            'sub_mp_app_id' => 'sub_mp_app_id',
            'sub_mini_app_id' => 'sub_mini_app_id',
            'sub_app_id' => 'sub_app_id',
        ]));

        expect($config->getAppIdByType(AppType::MP))->toBe('mp_app_id');
        expect($config->getAppIdByType(AppType::MINI))->toBe('mini_app_id');
        expect($config->getAppIdByType(AppType::APP))->toBe('app_id');
        // 未知值按 MP 处理（历史行为）
        expect($config->getAppIdByType('foo'))->toBe('mp_app_id');
    });

    it('按类型获取子应用 ID', function () {
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
        $config = new WechatConfig(array_merge($validConfig, [
            'mp_app_id' => 'mp_app_id',
            'mini_app_id' => 'mini_app_id',
            'app_id' => 'app_id',
            'sub_mp_app_id' => 'sub_mp_app_id',
            'sub_mini_app_id' => 'sub_mini_app_id',
            'sub_app_id' => 'sub_app_id',
        ]));

        expect($config->getSubAppIdByType(AppType::MP))->toBe('sub_mp_app_id');
        expect($config->getSubAppIdByType(AppType::MINI))->toBe('sub_mini_app_id');
        expect($config->getSubAppIdByType(AppType::APP))->toBe('sub_app_id');
        // 未知值按 MP 处理（历史行为）
        expect($config->getSubAppIdByType('foo'))->toBe('sub_mp_app_id');
    });

    it('可选', function () {
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
        $config = new WechatConfig(array_merge($validConfig, [
            'mp_app_id' => 'mp_app_id',
            'mini_app_id' => 'mini_app_id',
            'app_id' => 'app_id',
            'mch_secret_key_v2' => 'v2_key',
            'sub_mp_app_id' => 'sub_mp_app_id',
            'sub_mini_app_id' => 'sub_mini_app_id',
            'sub_app_id' => 'sub_app_id',
        ]));

        expect($config->getMpAppId())->toBe('mp_app_id');
        expect($config->getMiniAppId())->toBe('mini_app_id');
        expect($config->getAppId())->toBe('app_id');
        expect($config->getMchSecretKeyV2())->toBe('v2_key');
        expect($config->getSubMpAppId())->toBe('sub_mp_app_id');
        expect($config->getSubMiniAppId())->toBe('sub_mini_app_id');
        expect($config->getSubAppId())->toBe('sub_app_id');
    });

    it('可选返回 null', function () {
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
        $config = new WechatConfig($validConfig);

        expect($config->getMpAppId())->toBeNull();
        expect($config->getMiniAppId())->toBeNull();
        expect($config->getAppId())->toBeNull();
        expect($config->getMchSecretKeyV2())->toBeNull();
        expect($config->getSubMpAppId())->toBeNull();
        expect($config->getSubMiniAppId())->toBeNull();
        expect($config->getSubAppId())->toBeNull();
        expect($config->getSubMchId())->toBeNull();
    });

    it('服务商 ID', function () {
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
        $config = new WechatConfig(array_merge($validConfig, [
            'service_id' => '12345678901234567890123456789012',
        ]));

        expect($config->getServiceId())->toBe('12345678901234567890123456789012');
    });

    it('服务商 ID 返回 null', function () {
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
        $config = new WechatConfig($validConfig);

        expect($config->getServiceId())->toBeNull();
    });
});

describe('证书管理', function () {
    it('构造时导入证书管理器', function () {
        CertManager::clearCache();

        $tenant = 'test-import';
        $config = new WechatConfig([
            'mch_id' => 'test-mch',
            'mch_secret_key' => str_repeat('a', 32),
            'mch_secret_cert' => 'test-cert',
            'mch_public_cert_path' => 'test-path',
            'wechat_public_cert_path' => [
                'SERIAL1' => 'cert-content-1',
                'SERIAL2' => 'cert-content-2',
            ],
        ], $tenant);

        expect(CertManager::wechatGetCertBySerial($tenant, 'SERIAL1'))->toEqual('cert-content-1');
        expect(CertManager::wechatGetCertBySerial($tenant, 'SERIAL2'))->toEqual('cert-content-2');
    });
});

describe('虚拟支付', function () {
    it('默认空对象', function () {
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
        $config = new WechatConfig($validConfig);

        $vp = $config->getVirtualPay();
        expect($vp)->toBeInstanceOf(WechatConfigVirtualPay::class);
        expect($vp->getAppKey())->toBeNull();
        expect($vp->getSandboxAppKey())->toBeNull();
        expect($vp->getOfferId())->toBeNull();
    })->group('VirtualPay');

    it('数组配置构造', function () {
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
        $config = new WechatConfig(array_merge($validConfig, [
            'virtual_pay' => [
                'app_key' => 'vp-app-key',
                'sandbox_app_key' => 'vp-sandbox-key',
                'offer_id' => 'vp-offer-123',
            ],
        ]));

        $vp = $config->getVirtualPay();
        expect($vp->getAppKey())->toBe('vp-app-key');
        expect($vp->getSandboxAppKey())->toBe('vp-sandbox-key');
        expect($vp->getOfferId())->toBe('vp-offer-123');
    })->group('VirtualPay');

    it('沙箱回退', function () {
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
        $config = new WechatConfig(array_merge($validConfig, [
            'virtual_pay' => [
                'app_key' => 'vp-app-key',
                'sandbox_app_key' => 'vp-sandbox-key',
            ],
        ]));

        $vp = $config->getVirtualPay();
        expect($vp->getAppKey(1))->toBe('vp-sandbox-key');
    })->group('VirtualPay');

    it('setter 设置', function () {
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
        $config = new WechatConfig($validConfig);

        $vp = new WechatConfigVirtualPay();
        $vp->setAppKey('direct-key');
        $vp->setOfferId('direct-offer');

        $config->setVirtualPay($vp);

        $result = $config->getVirtualPay();
        expect($result->getAppKey())->toBe('direct-key');
        expect($result->getOfferId())->toBe('direct-offer');
    })->group('VirtualPay');

    it('数组 setter 设置', function () {
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
        $config = new WechatConfig($validConfig);

        $config->setVirtualPay([
            'app_key' => 'array-key',
            'offer_id' => 'array-offer',
        ]);

        $vp = $config->getVirtualPay();
        expect($vp->getAppKey())->toBe('array-key');
        expect($vp->getOfferId())->toBe('array-offer');
    })->group('VirtualPay');

    it('已配置需 app_key 与 app_secret', function () {
        $validConfig = [
            'mch_id' => 'test_mch_id',
            'mch_secret_key' => '12345678901234567890123456789012',
            'mch_secret_cert' => 'test_cert',
            'mch_public_cert_path' => 'test_path',
            'notify_url' => 'https://test.com',
        ];
        $config = new WechatConfig(array_merge($validConfig, [
            'virtual_pay' => [
                'app_key' => 'vp-app-key',
                'offer_id' => 'vp-offer-123',
            ],
        ]));

        expect($config->getVirtualPay()->isConfigured())->toBeFalse();

        $config->setVirtualPay([
            'app_key' => 'vp-app-key',
            'app_secret' => 'vp-app-secret',
        ]);

        expect($config->getVirtualPay()->isConfigured())->toBeTrue();
    })->group('VirtualPay');

    it('仅虚拟支付配置跳过微信支付校验', function () {
        $config = new WechatConfig([
            'mini_app_id' => 'wx1234567890abcdef',
            'virtual_pay' => [
                'offer_id' => 'vp-offer-123',
                'app_key' => 'vp-app-key',
                'app_secret' => 'vp-app-secret',
                'callback_token' => 'vp-callback-token',
                'encoding_aes_key' => 'vp-encoding-aes-key',
            ],
        ]);

        $config->validate();

        expect($config->getMchId())->toBe('');
        expect($config->getMchSecretKey())->toBe('');
        expect($config->getMchSecretCert())->toBe('');
        expect($config->getMchPublicCertPath())->toBe('');
    })->group('VirtualPay');

    it('服务商模式下跳过子商户号校验', function () {
        $config = new WechatConfig([
            'mode' => PayMode::SERVICE,
            'virtual_pay' => [
                'app_key' => 'vp-app-key',
                'app_secret' => 'vp-app-secret',
            ],
        ]);

        $config->validate();

        expect($config->getMode())->toBe(PayMode::SERVICE);
    })->group('VirtualPay');

    it('未配 app_secret 时仍需微信支付凭证', function () {

        $config = new WechatConfig([
            'virtual_pay' => [
                'offer_id' => 'vp-offer-123',
                'app_key' => 'vp-app-key',
            ],
        ]);
        $config->validate();
    })->group('VirtualPay')->throws(InvalidConfigException::class, '配置异常: 缺少微信配置 -- [mch_id]');

    it('部分微信支付凭证仍校验', function () {

        $config = new WechatConfig([
            'mch_id' => 'test_mch_id',
            'virtual_pay' => [
                'app_key' => 'vp-app-key',
                'app_secret' => 'vp-app-secret',
            ],
        ]);
        $config->validate();
    })->group('VirtualPay')->throws(InvalidConfigException::class, '配置异常: 缺少微信配置 -- [mch_secret_key]');
});