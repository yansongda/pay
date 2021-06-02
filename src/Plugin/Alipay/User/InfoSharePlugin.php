<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\User;

use Closure;
use Yansongda\Supports\Collection;

class InfoSharePlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->merge([
            'method' => 'alipay.user.info.share',
            'auth_token' => $params['auth_token'] ?? '',
        ]);

        return $next($params, $payload);
    }
}
