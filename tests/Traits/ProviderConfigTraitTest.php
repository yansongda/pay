<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Config\ProviderConfigInterface;
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Pay as PayFacade;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\ProviderConfigTrait;
use Yansongda\Supports\Collection;

class ProviderConfigTraitStub
{
    use ProviderConfigTrait;
}

class ProviderConfigTraitTest extends TestCase
{
    public function testGetTenantDefault(): void
    {
        self::assertSame('default', ProviderConfigTraitStub::getTenant());
    }

    public function testGetTenantCustom(): void
    {
        self::assertSame('yansongda', ProviderConfigTraitStub::getTenant(['_config' => 'yansongda']));
    }

    public function testGetProviderConfigDefault(): void
    {
        self::assertSame(
            PayFacade::get(ConfigInterface::class)->get('alipay.'.ProviderConfigTraitStub::getTenant()),
            ProviderConfigTraitStub::getProviderConfig('alipay')
        );
    }

    public function testGetProviderConfigAlipay(): void
    {
        $defaultConfig = ProviderConfigTraitStub::getProviderConfig('alipay');
        self::assertInstanceOf(AlipayConfig::class, $defaultConfig);

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
            self::fail('Expected alipay config to be '.AlipayConfig::class);
        }

        self::assertSame('yansongda', $defaultConfig->getAppId());

        $customConfig = ProviderConfigTraitStub::getProviderConfig('alipay', ['_config' => 'c1']);
        if (!$customConfig instanceof AlipayConfig) {
            self::fail('Expected alipay tenant config to be '.AlipayConfig::class);
        }

        self::assertSame('yansongda-c1', $customConfig->getAppId());
    }

    public function testGetProviderConfigWechat(): void
    {
        $defaultConfig = ProviderConfigTraitStub::getProviderConfig('wechat', []);
        self::assertInstanceOf(WechatConfig::class, $defaultConfig);

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
            self::fail('Expected wechat config to be '.WechatConfig::class);
        }

        self::assertSame('wx-default', $defaultConfig->getMpAppId());

        $customConfig = ProviderConfigTraitStub::getProviderConfig('wechat', ['_config' => 'c1']);
        if (!$customConfig instanceof WechatConfig) {
            self::fail('Expected wechat tenant config to be '.WechatConfig::class);
        }

        self::assertSame('wx-c1', $customConfig->getMpAppId());
    }

    public function testGetProviderConfigUnipay(): void
    {
        $defaultConfig = ProviderConfigTraitStub::getProviderConfig('unipay');
        self::assertInstanceOf(UnipayConfig::class, $defaultConfig);

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
            self::fail('Expected unipay config to be '.UnipayConfig::class);
        }

        self::assertSame('yansongda', $defaultConfig->getMchId());

        $customConfig = ProviderConfigTraitStub::getProviderConfig('unipay', ['_config' => 'c1']);
        if (!$customConfig instanceof UnipayConfig) {
            self::fail('Expected unipay tenant config to be '.UnipayConfig::class);
        }

        self::assertSame('yansongda-c1', $customConfig->getMchId());
    }

    public function testGetProviderConfigCustomTenant(): void
    {
        self::assertSame(
            PayFacade::get(ConfigInterface::class)->get('wechat.service_provider'),
            ProviderConfigTraitStub::getProviderConfig('wechat', ['_config' => 'service_provider'])
        );
    }

    public function testGetProviderConfigReturnsProviderConfigObject(): void
    {
        $config = new class implements ProviderConfigInterface {
            public function getTenant(): string
            {
                return 'default';
            }

            public function getMode(): int
            {
                return PayFacade::MODE_NORMAL;
            }

            public function get(?string $key = null, mixed $default = null): mixed
            {
                return $default;
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

        self::assertSame($config, ProviderConfigTraitStub::getProviderConfig('custom'));
    }

    public function testGetRadarUrlNull(): void
    {
        $config = ProviderConfigTraitStub::getProviderConfig('wechat');
        self::assertNull(ProviderConfigTraitStub::getRadarUrl($config, null));
        self::assertNull(ProviderConfigTraitStub::getRadarUrl($config, new Collection()));
    }

    public function testGetRadarUrlNormal(): void
    {
        $config = ProviderConfigTraitStub::getProviderConfig('wechat');
        self::assertSame(
            'https://yansongda.cn',
            ProviderConfigTraitStub::getRadarUrl($config, new Collection(['_url' => 'https://yansongda.cn']))
        );
    }

    public function testGetRadarUrlService(): void
    {
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
            'mode' => PayFacade::MODE_SERVICE,
        ]);
        self::assertSame(
            'https://yansongda.cnaaa',
            ProviderConfigTraitStub::getRadarUrl(
                $serviceConfig,
                new Collection(['_url' => 'https://yansongda.cn', '_service_url' => 'https://yansongda.cnaaa'])
            )
        );
    }
}
