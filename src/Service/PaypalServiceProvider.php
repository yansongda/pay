<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Service\AbstractServiceProvider;
use Yansongda\Pay\Provider\Paypal;

class PaypalServiceProvider extends AbstractServiceProvider
{
    protected function getProviderClass(): string
    {
        return Paypal::class;
    }

    protected function getProviderName(): string
    {
        return 'paypal';
    }
}
