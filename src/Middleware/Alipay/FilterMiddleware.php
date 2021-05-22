<?php

declare(strict_types=1);

namespace Yansongda\Pay\Middleware\Alipay;

use Closure;
use Yansongda\Supports\Collection;

class FilterMiddleware
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->filter(function ($v, $k) {
            return '' !== $v && !is_null($v) && 'sign' != $k && '@' != substr($v, 0, 1);
        });

        return $next($params, $payload);
    }
}
