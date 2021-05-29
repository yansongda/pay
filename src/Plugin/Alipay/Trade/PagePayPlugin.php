<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Trade;

use Closure;
use Yansongda\Supports\Collection;

class PagePayPlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->merge([
            'method' => 'alipay.trade.page.pay',
            'biz_content' => json_encode(array_merge(
                [
                    'product_code' => 'FAST_INSTANT_TRADE_PAY',
                ],
                $params
            )),
        ]);

        return $next($params, $payload);
    }
}
