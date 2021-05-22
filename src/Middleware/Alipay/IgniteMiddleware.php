<?php

declare(strict_types=1);

namespace Yansongda\Pay\Middleware\Alipay;

use Closure;
use Yansongda\Supports\Collection;

class IgniteMiddleware
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload->merge([
            'app_id',
            'method',
            'format',
            'charset',
            'sign_type',
            'sign',
            'timestamp',
            'version',
            'notify_url',
            'app_auth_token',
            'biz_content'
        ]);

        return $next($params, $payload);
    }
}
