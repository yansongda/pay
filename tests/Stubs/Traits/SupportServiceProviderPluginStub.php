<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Stubs\Traits;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Traits\SupportServiceProviderTrait;

class SupportServiceProviderPluginStub
{
    use SupportServiceProviderTrait;

    public function assembly(Rocket $rocket)
    {
        $this->loadAlipayServiceProvider($rocket);
    }
}
