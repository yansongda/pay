<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Trade;

use Closure;
use Yansongda\Supports\Collection;

class AppPayPlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->merge([
            'method' => 'alipay.trade.app.pay',
            'biz_content' => json_encode(array_merge(
                [
                    'product_code' => 'QUICK_MSECURITY_PAY',
                ],
                $params
            )),
        ]);

        return $next($params, $payload);
    }
}
