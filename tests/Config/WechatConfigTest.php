<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Config\WechatConfigVirtualPay;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Enum\Wechat\AppType;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

it('construct valid config', function () {
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

it('construct with tenant', function () {
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

it('mode sandbox', function () {
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

it('mode with int value', function () {
    $validConfig = [
        'mch_id' => 'test_mch_id',
        'mch_secret_key' => '12345678901234567890123456789012',
        'mch_secret_cert' => 'test_cert',
        'mch_public_cert_path' => 'test_path',
        'notify_url' => 'https://test.com',
    ];
    $config = new WechatConfig(array_merge($validConfig, [
        'mode' => 1,
    ]));

    expect($config->getMode())->toBe(PayMode::SANDBOX);
});

it('get app id by type', function () {
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

it('get sub app id by type', function () {
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

it('construct without notify url', function () {
    $config = new WechatConfig([
        'mch_id' => 'test_mch_id',
        'mch_secret_key' => '12345678901234567890123456789012',
        'mch_secret_cert' => 'test_cert',
        'mch_public_cert_path' => 'test_path',
    ]);

    expect($config->getNotifyUrl())->toBe('');
});

it('construct missing required', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionMessage('配置异常: 缺少微信配置 -- [mch_id]');

    $config = new WechatConfig([
        // missing mch_id
        'mch_secret_key' => '12345678901234567890123456789012',
        'mch_secret_cert' => 'test_cert',
        'mch_public_cert_path' => 'test_path',
        'notify_url' => 'https://test.com',
    ]);
    $config->validate();
});

it('construct invalid secret key length', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionMessage('配置异常: mch_secret_key 长度应为 32 字节');

    $config = new WechatConfig([
        'mch_id' => 'test_mch_id',
        'mch_secret_key' => 'short_key', // 9 bytes, not 32
        'mch_secret_cert' => 'test_cert',
        'mch_public_cert_path' => 'test_path',
        'notify_url' => 'https://test.com',
    ]);
    $config->validate();
});

it('construct service mode missing sub mch id', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionMessage('配置异常: 服务商模式下缺少 [sub_mch_id]');

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
});

it('construct service mode with sub mch id', function () {
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

it('optional getters', function () {
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

it('optional getters null', function () {
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

it('service id getter', function () {
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

it('service id getter null', function () {
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

it('wechat config init imports to cert manager', function () {
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

it('virtual pay default is empty object', function () {
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

it('virtual pay from array config', function () {
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

it('virtual pay sandbox fallback', function () {
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

it('virtual pay set via setter', function () {
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

it('virtual pay set via array setter', function () {
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

it('virtual pay is configured requires app key and app secret', function () {
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

it('virtual pay only config skips wechat pay validation', function () {
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

it('virtual pay only config skips sub mch id validation in service mode', function () {
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

it('virtual pay without app secret still requires wechat pay credentials', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionMessage('配置异常: 缺少微信配置 -- [mch_id]');

    $config = new WechatConfig([
        'virtual_pay' => [
            'offer_id' => 'vp-offer-123',
            'app_key' => 'vp-app-key',
        ],
    ]);
    $config->validate();
})->group('VirtualPay');

it('partial wechat pay credentials still validate with virtual pay', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionMessage('配置异常: 缺少微信配置 -- [mch_secret_key]');

    $config = new WechatConfig([
        'mch_id' => 'test_mch_id',
        'virtual_pay' => [
            'app_key' => 'vp-app-key',
            'app_secret' => 'vp-app-secret',
        ],
    ]);
    $config->validate();
})->group('VirtualPay');

it('invalid mode throws exception', function () {
    $validConfig = [
        'mch_id' => 'test_mch_id',
        'mch_secret_key' => '12345678901234567890123456789012',
        'mch_secret_cert' => 'test_cert',
        'mch_public_cert_path' => 'test_path',
        'notify_url' => 'https://test.com',
    ];
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_PROVIDER_INVALID);
    $this->expectExceptionMessage('配置异常: [mode] 配置不合法');

    $config = new WechatConfig(array_merge($validConfig, [
        'mode' => 99999,
    ]));
    $config->validate();
});

