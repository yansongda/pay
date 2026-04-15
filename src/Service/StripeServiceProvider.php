<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Provider\Stripe;

class StripeServiceProvider extends AbstractServiceProvider
{
    protected function getProviderClass(): string
    {
        return Stripe::class;
    }

    protected function getProviderName(): string
    {
        return 'stripe';
    }
}
