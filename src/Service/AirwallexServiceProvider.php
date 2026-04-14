<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Artful\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Airwallex;

class AirwallexServiceProvider implements ServiceProviderInterface
{
    public function register(mixed $data = null): void
    {
        $service = new Airwallex();

        Pay::set(Airwallex::class, $service);
        Pay::set('airwallex', $service);
    }
}
