<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Open;

use Closure;
use Yansongda\Supports\Collection;

class AuthTokenAppPlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->merge([
            'method' => 'alipay.open.auth.token.app',
            'biz_content' => json_encode($params),
        ]);

        return $next($params, $payload);
    }
}
