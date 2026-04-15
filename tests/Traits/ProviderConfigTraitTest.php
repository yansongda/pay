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
        self::assertSame('service_provider', ProviderConfigTraitStub::getTenant(['_config' => 'service_provider']));
    }

    public function testGetProviderConfigDefault(): void
    {
        self::assertSame(
            PayFacade::get(ConfigInterface::class)->get('alipay', [])[ProviderConfigTraitStub::getTenant()] ?? [],
            ProviderConfigTraitStub::getProviderConfig('alipay')
        );
    }

    public function testGetProviderConfigCustomTenant(): void
    {
        self::assertSame(
            PayFacade::get(ConfigInterface::class)->get('wechat', [])['service_provider'] ?? [],
            ProviderConfigTraitStub::getProviderConfig('wechat', ['_config' => 'service_provider'])
        );
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
