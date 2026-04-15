<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Artful\Contract\ConfigInterface;
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
                'default' => ['name' => 'yansongda'],
                'c1' => ['age' => 28]
            ]
        ];
        PayFacade::config($config2);
        self::assertEquals(['name' => 'yansongda'], ProviderConfigTraitStub::getProviderConfig('alipay'));

        self::assertEquals(['age' => 28], ProviderConfigTraitStub::getProviderConfig('alipay', ['_config' => 'c1']));
    }

    public function testGetProviderConfigWechat(): void
    {
        self::assertArrayHasKey('mp_app_id', ProviderConfigTraitStub::getProviderConfig('wechat', []));

        $config2 = [
            'wechat' => [
                'default' => ['name' => 'yansongda'],
                'c1' => ['age' => 28]
            ]
        ];
        PayFacade::config(array_merge($config2, ['_force' => true]));
        self::assertEquals(['name' => 'yansongda'], ProviderConfigTraitStub::getProviderConfig('wechat', []));

        self::assertEquals(['age' => 28], ProviderConfigTraitStub::getProviderConfig('wechat', ['_config' => 'c1']));
    }

    public function testGetProviderConfigUnipay(): void
    {
        self::assertArrayHasKey('mch_id', ProviderConfigTraitStub::getProviderConfig('unipay'));

        PayFacade::clear();

        $config2 = [
            'unipay' => [
                'default' => ['name' => 'yansongda'],
                'c1' => ['age' => 28]
            ]
        ];
        PayFacade::config($config2);
        self::assertEquals(['name' => 'yansongda'], ProviderConfigTraitStub::getProviderConfig('unipay'));

        self::assertEquals(['age' => 28], ProviderConfigTraitStub::getProviderConfig('unipay', ['_config' => 'c1']));
    }

    public function testGetProviderConfigCustomTenant(): void
    {
        self::assertSame(
            PayFacade::get(ConfigInterface::class)->get('wechat', [])['service_provider'] ?? [],
            ProviderConfigTraitStub::getProviderConfig('wechat', ['_config' => 'service_provider'])
        );
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
