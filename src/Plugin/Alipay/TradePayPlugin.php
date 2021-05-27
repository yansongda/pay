<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Supports\Collection;

class TradePayPlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        return $next($params, $payload);
    }
}
