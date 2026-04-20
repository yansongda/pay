<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Pay\Config\ProviderConfigInterface;
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
            PayFacade::get(ConfigInterface::class)->get('alipay', [])[ProviderConfigTraitStub::getTenant()] ?? [],
            ProviderConfigTraitStub::getProviderConfig('alipay')
        );
    }

    public function testGetProviderConfigAlipay(): void
    {
        self::assertArrayHasKey('app_id', ProviderConfigTraitStub::getProviderConfig('alipay'));

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
        self::assertSame('yansongda', ProviderConfigTraitStub::getProviderConfig('alipay')['app_id']);

        self::assertSame('yansongda-c1', ProviderConfigTraitStub::getProviderConfig('alipay', ['_config' => 'c1'])['app_id']);
    }

    public function testGetProviderConfigWechat(): void
    {
        self::assertArrayHasKey('mp_app_id', ProviderConfigTraitStub::getProviderConfig('wechat', []));

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
        self::assertSame('wx-default', ProviderConfigTraitStub::getProviderConfig('wechat', [])['mp_app_id']);

        self::assertSame('wx-c1', ProviderConfigTraitStub::getProviderConfig('wechat', ['_config' => 'c1'])['mp_app_id']);
    }

    public function testGetProviderConfigUnipay(): void
    {
        self::assertArrayHasKey('mch_id', ProviderConfigTraitStub::getProviderConfig('unipay'));

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
        self::assertSame('yansongda', ProviderConfigTraitStub::getProviderConfig('unipay')['mch_id']);

        self::assertSame('yansongda-c1', ProviderConfigTraitStub::getProviderConfig('unipay', ['_config' => 'c1'])['mch_id']);
    }

    public function testGetProviderConfigCustomTenant(): void
    {
        self::assertSame(
            PayFacade::get(ConfigInterface::class)->get('wechat', [])['service_provider'] ?? [],
            ProviderConfigTraitStub::getProviderConfig('wechat', ['_config' => 'service_provider'])
        );
    }

    public function testGetProviderConfigUsesExplicitArrayExportContract(): void
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

        self::assertSame(['foo' => 'bar'], ProviderConfigTraitStub::getProviderConfig('custom'));
    }

    public function testGetRadarUrlNull(): void
    {
        self::assertNull(ProviderConfigTraitStub::getRadarUrl([], null));
        self::assertNull(ProviderConfigTraitStub::getRadarUrl([], new Collection()));
    }

    public function testGetRadarUrlNormal(): void
    {
        self::assertSame(
            'https://yansongda.cn',
            ProviderConfigTraitStub::getRadarUrl([], new Collection(['_url' => 'https://yansongda.cn']))
        );
    }

    public function testGetRadarUrlService(): void
    {
        self::assertSame(
            'https://yansongda.cnaaa',
            ProviderConfigTraitStub::getRadarUrl(
                ['mode' => PayFacade::MODE_SERVICE],
                new Collection(['_url' => 'https://yansongda.cn', '_service_url' => 'https://yansongda.cnaaa'])
            )
        );
    }
}
