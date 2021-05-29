<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Trade;

use Closure;
use Yansongda\Supports\Collection;

class PageRefundPlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->merge([
            'method' => 'alipay.trade.page.refund',
            'biz_content' => json_encode($params),
        ]);

        return $next($params, $payload);
    }
}
