<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Artful\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Paypal;

class PaypalServiceProvider implements ServiceProviderInterface
{
    public function register(mixed $data = null): void
    {
        $service = new Paypal();

        Pay::set(Paypal::class, $service);
        Pay::set('paypal', $service);
    }
}
