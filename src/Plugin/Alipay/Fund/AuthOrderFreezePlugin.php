<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund;

use Closure;
use Yansongda\Supports\Collection;

class AuthOrderFreezePlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->merge([
            'method' => 'alipay.fund.auth.order.freeze',
            'biz_content' => json_encode(array_merge(
                [
                    'product_code' => 'PRE_AUTH',
                ],
                $params
            )),
        ]);

        return $next($params, $payload);
    }
}
