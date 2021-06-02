<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Data;

use Closure;
use Yansongda\Supports\Collection;

class BillEreceiptApplyPlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->merge([
            'method' => 'alipay.data.bill.ereceipt.apply',
            'biz_content' => json_encode(array_merge(
                [
                    'type' => 'FUND_DETAIL',
                ],
                $params
            )),
        ]);

        return $next($params, $payload);
    }
}
