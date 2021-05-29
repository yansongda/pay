<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Ebpp;

use Closure;
use Yansongda\Supports\Collection;

class PdeductBillPayStatusPlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->merge([
            'method' => 'alipay.ebpp.pdeduct.bill.pay.status',
            'biz_content' => json_encode($params),
        ]);

        return $next($params, $payload);
    }
}
