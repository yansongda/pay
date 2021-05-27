<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Closure;
use Yansongda\Supports\Collection;

interface PluginInterface
{
    public function apply(array $params, Collection $payload, Closure $next): Collection;
}
