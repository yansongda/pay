<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund;

use Closure;
use Yansongda\Supports\Collection;

class AuthOrderUnfreezePlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->merge([
            'method' => 'alipay.fund.auth.order.unfreeze',
            'biz_content' => json_encode($params),
        ]);

        return $next($params, $payload);
    }
}
