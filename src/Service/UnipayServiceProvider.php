<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Service\AbstractServiceProvider;
use Yansongda\Pay\Provider\Unipay;

class UnipayServiceProvider extends AbstractServiceProvider
{
    protected function getProviderClass(): string
    {
        return Unipay::class;
    }

    protected function getProviderName(): string
    {
        return 'unipay';
    }
}
