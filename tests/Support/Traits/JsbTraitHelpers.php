<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use Yansongda\Pay\Config\JsbConfig;
use Yansongda\Pay\Tests\Stubs\JsbTraitStub;

/**
 * 江苏银行 Trait 测试 Helper。
 */
trait JsbTraitHelpers
{
    private function getJsbConfig(): JsbConfig
    {
        $config = JsbTraitStub::getProviderConfig('jsb', []);

        expect($config)->toBeInstanceOf(JsbConfig::class);

        return $config;
    }
}
