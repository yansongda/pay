<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Supports\Collection;

class TradePayPlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $biz = array_merge(['product_code' => 'QUICK_WAP_PAY'], $params);

        $payload = $payload->merge([
            'method' => 'alipay.trade.wap.pay',
            'biz_content' => json_encode($biz),
        ]);

        return $next($params, $payload);
    }
}
