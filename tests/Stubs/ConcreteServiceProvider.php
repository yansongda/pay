<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Stubs;

use Yansongda\Pay\Contract\ProviderInterface;
use Yansongda\Pay\Service\AbstractServiceProvider;

/**
 * 最小可用 ServiceProvider 桩，用于验证注册表写入与按类名/别名解析。
 */
class ConcreteServiceProvider extends AbstractServiceProvider
{
    protected function makeService(): ProviderInterface
    {
        return new ConcreteProvider();
    }

    protected function getProviderName(): string
    {
        return 'concrete_provider';
    }
}
