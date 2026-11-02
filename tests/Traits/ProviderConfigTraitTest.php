<?php

declare(strict_types=1);

use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Contract\ProviderConfigInterface;
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Pay as PayFacade;
use Yansongda\Pay\Traits\ProviderConfigTrait;
use Yansongda\Supports\Collection;

class ProviderConfigTraitStub
{
use ProviderConfigTrait;
}

it('获取默认租户', function () {
    expect(ProviderConfigTraitStub::getTenant())->toBe('default');
});

it('获取自定义租户', function () {
    expect(ProviderConfigTraitStub::getTenant(['_config' => 'yansongda']))->toBe('yansongda');
});

it('获取默认 provider 配置', function () {
    expect(ProviderConfigTraitStub::getProviderConfig('alipay'))->toBe(PayFacade::get(ConfigInterface::class)->get('alipay.'.ProviderConfigTraitStub::getTenant()));
});

it('获取支付宝 provider 配置', function () {
    $defaultConfig = ProviderConfigTraitStub::getProviderConfig('alipay');
    expect($defaultConfig)->toBeInstanceOf(AlipayConfig::class);

    PayFacade::clear();

    $config2 = [
        'alipay' => [
            'default' => [
                'app_id' => 'yansongda',
                'app_secret_cert' => 'secret',
                'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
                'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
                'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
            ],
            'c1' => [
                'app_id' => 'yansongda-c1',
                'app_secret_cert' => 'secret',
                'app_public_cert_path' => __DIR__.'/../Cert/alipayAppPublicCert.crt',
                'alipay_public_cert_path' => __DIR__.'/../Cert/alipayPublicCert.crt',
                'alipay_root_cert_path' => __DIR__.'/../Cert/alipayRootCert.crt',
            ],
        ],
    ];
    PayFacade::config($config2);
    $defaultConfig = ProviderConfigTraitStub::getProviderConfig('alipay');
    if (!$defaultConfig instanceof AlipayConfig) {
        $this->fail('Expected alipay config to be '.AlipayConfig::class);
    }

    expect($defaultConfig->getAppId())->toBe('yansongda');

    $customConfig = ProviderConfigTraitStub::getProviderConfig('alipay', ['_config' => 'c1']);
    if (!$customConfig instanceof AlipayConfig) {
        $this->fail('Expected alipay tenant config to be '.AlipayConfig::class);
    }

    expect($customConfig->getAppId())->toBe('yansongda-c1');
});

it('获取微信 provider 配置', function () {
    $defaultConfig = ProviderConfigTraitStub::getProviderConfig('wechat', []);
    expect($defaultConfig)->toBeInstanceOf(WechatConfig::class);

    $config2 = [
        'wechat' => [
            'default' => [
                'mch_id' => '1600314069',
                'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                'mch_secret_cert' => __DIR__.'/../Cert/wechatAppPrivateKey.pem',
                'mch_public_cert_path' => __DIR__.'/../Cert/wechatAppPublicKey.pem',
                'notify_url' => 'https://pay.yansongda.cn',
                'mp_app_id' => 'wx-default',
            ],
            'c1' => [
                'mch_id' => '1600314070',
                'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                'mch_secret_cert' => __DIR__.'/../Cert/wechatAppPrivateKey.pem',
                'mch_public_cert_path' => __DIR__.'/../Cert/wechatAppPublicKey.pem',
                'notify_url' => 'https://pay.yansongda.cn',
                'mp_app_id' => 'wx-c1',
            ],
        ],
    ];
    PayFacade::config(array_merge($config2, ['_force' => true]));
    $defaultConfig = ProviderConfigTraitStub::getProviderConfig('wechat', []);
    if (!$defaultConfig instanceof WechatConfig) {
        $this->fail('Expected wechat config to be '.WechatConfig::class);
    }

    expect($defaultConfig->getMpAppId())->toBe('wx-default');

    $customConfig = ProviderConfigTraitStub::getProviderConfig('wechat', ['_config' => 'c1']);
    if (!$customConfig instanceof WechatConfig) {
        $this->fail('Expected wechat tenant config to be '.WechatConfig::class);
    }

    expect($customConfig->getMpAppId())->toBe('wx-c1');
});

it('获取银联 provider 配置', function () {
    $defaultConfig = ProviderConfigTraitStub::getProviderConfig('unipay');
    expect($defaultConfig)->toBeInstanceOf(UnipayConfig::class);

    PayFacade::clear();

    $config2 = [
        'unipay' => [
            'default' => [
                'mch_cert_path' => __DIR__.'/../Cert/unipayAppCert.pfx',
                'mch_cert_password' => '000000',
                'mch_id' => 'yansongda',
            ],
            'c1' => [
                'mch_cert_path' => __DIR__.'/../Cert/unipayAppCert.pfx',
                'mch_cert_password' => '000000',
                'mch_id' => 'yansongda-c1',
            ],
        ],
    ];
    PayFacade::config($config2);
    $defaultConfig = ProviderConfigTraitStub::getProviderConfig('unipay');
    if (!$defaultConfig instanceof UnipayConfig) {
        $this->fail('Expected unipay config to be '.UnipayConfig::class);
    }

    expect($defaultConfig->getMchId())->toBe('yansongda');

    $customConfig = ProviderConfigTraitStub::getProviderConfig('unipay', ['_config' => 'c1']);
    if (!$customConfig instanceof UnipayConfig) {
        $this->fail('Expected unipay tenant config to be '.UnipayConfig::class);
    }

    expect($customConfig->getMchId())->toBe('yansongda-c1');
});

it('获取自定义租户 provider 配置', function () {
    expect(ProviderConfigTraitStub::getProviderConfig('wechat', ['_config' => 'service_provider']))->toBe(PayFacade::get(ConfigInterface::class)->get('wechat.service_provider'));
});

it('获取 provider 配置返回配置对象', function () {
    $config = new class implements ProviderConfigInterface {
        public function getTenant(): string
        {
            return 'default';
        }

        public function getMode(): PayMode
        {
            return PayMode::NORMAL;
        }

        public function get(?string $key = null, mixed $default = null): mixed
        {
            return $default;
        }

        public function validate(): void
        {
        }

        public function toArray(): array
        {
            return ['foo' => 'bar'];
        }
    };

    PayFacade::config([
        '_force' => true,
        'custom' => [
            'default' => $config,
        ],
    ]);

    expect(ProviderConfigTraitStub::getProviderConfig('custom'))->toBe($config);
});

it('radar URL 为 null', function () {
    $config = ProviderConfigTraitStub::getProviderConfig('wechat');
    expect(ProviderConfigTraitStub::getRadarUrl($config, null))->toBeNull();
    expect(ProviderConfigTraitStub::getRadarUrl($config, new Collection()))->toBeNull();
});

it('获取普通 radar URL', function () {
    $config = ProviderConfigTraitStub::getProviderConfig('wechat');
    expect(ProviderConfigTraitStub::getRadarUrl($config, new Collection(['_url' => 'https://yansongda.cn'])))->toBe('https://yansongda.cn');
});

it('获取服务商 radar URL', function () {
    $serviceConfig = new WechatConfig([
        'app_id' => 'yansongda',
        'mp_app_id' => 'wx55955316af4ef13',
        'mch_id' => '1600314069',
        'mini_app_id' => 'wx55955316af4ef14',
        'mch_secret_key_v2' => 'yansongda',
        'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
        'mch_secret_cert' => __DIR__.'/../Cert/wechatAppPrivateKey.pem',
        'mch_public_cert_path' => __DIR__.'/../Cert/wechatAppPublicKey.pem',
        'notify_url' => 'https://pay.yansongda.cn',
        'wechat_public_cert_path' => [
            '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/../Cert/wechatAppPublicKey.pem',
            'yansongda' => __DIR__.'/../Cert/wechatPublicKey.crt',
        ],
        'sub_mch_id' => '1600314070',
        'mode' => PayMode::SERVICE,
    ]);
    expect(ProviderConfigTraitStub::getRadarUrl(
            $serviceConfig,
            new Collection(['_url' => 'https://yansongda.cn', '_service_url' => 'https://yansongda.cnaaa'])
        ))->toBe('https://yansongda.cnaaa');
});

it('访问时校验 provider 配置', function () {
    PayFacade::config([
        'alipay' => [
            'default' => [
                'app_id' => 'x',
            ],
        ],
        '_force' => true,
    ]);

    ProviderConfigTraitStub::getProviderConfig('alipay', []);
})->throws(InvalidConfigException::class, '配置异常: 缺少支付宝配置 -- [app_secret_cert]');

