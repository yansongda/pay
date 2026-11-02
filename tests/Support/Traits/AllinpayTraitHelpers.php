<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use Yansongda\Pay\Config\AllinpayConfig;
use Yansongda\Pay\Tests\Stubs\AllinpayTraitStub;

/**
 * 通联支付 Trait 测试 Helper。
 */
trait AllinpayTraitHelpers
{
    private function getConfig(): AllinpayConfig
    {
        $config = AllinpayTraitStub::getProviderConfig('allinpay', []);

        expect($config)->toBeInstanceOf(AllinpayConfig::class);

        return $config;
    }
}
