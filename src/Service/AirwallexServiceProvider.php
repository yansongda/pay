<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Airwallex;

class AirwallexServiceProvider extends AbstractServiceProvider
{
    protected function getProviderClass(): string
    {
        return Airwallex::class;
    }

    protected function getProviderName(): string
    {
        return Pay::PROVIDER_AIRWALLEX;
    }
}
