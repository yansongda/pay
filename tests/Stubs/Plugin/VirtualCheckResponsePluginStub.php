<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Stubs\Plugin;

use Closure;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\CheckResponsePlugin;

class VirtualCheckResponsePluginStub extends CheckResponsePlugin
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        return $next($rocket);
    }
}
