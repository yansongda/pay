<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use Yansongda\Pay\Config\PaypalConfig;
use Yansongda\Pay\Tests\Stubs\PaypalTraitStub;

/**
 * PayPal Trait 测试 Helper。
 */
trait PaypalTraitHelpers
{
    private function getPaypalConfig(): PaypalConfig
    {
        $config = PaypalTraitStub::getProviderConfig('paypal', []);

        expect($config)->toBeInstanceOf(PaypalConfig::class);

        return $config;
    }
}
