<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Artful\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Stripe;

class StripeServiceProvider implements ServiceProviderInterface
{
    public function register(mixed $data = null): void
    {
        $service = new Stripe();

        Pay::set(Stripe::class, $service);
        Pay::set('stripe', $service);
    }
}
