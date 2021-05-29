<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Data;

use Closure;
use Yansongda\Supports\Collection;

class BillEreceiptQueryPlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->merge([
            'method' => 'alipay.data.bill.ereceipt.query',
            'biz_content' => json_encode($params),
        ]);

        return $next($params, $payload);
    }
}
