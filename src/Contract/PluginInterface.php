<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Closure;
use Yansongda\Pay\Rocket;

interface PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket;
}
