<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Service\AbstractServiceProvider;
use Yansongda\Pay\Provider\Alipay;

class AlipayServiceProvider extends AbstractServiceProvider
{
    protected function getProviderClass(): string
    {
        return Alipay::class;
    }

    protected function getProviderName(): string
    {
        return 'alipay';
    }
}
