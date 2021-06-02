<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund;

use Closure;
use Yansongda\Supports\Collection;

class TransUniTransferPlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->merge([
            'method' => 'alipay.fund.trans.uni.transfer',
            'biz_content' => json_encode(array_merge(
                [
                    'product_code' => 'TRANS_ACCOUNT_NO_PWD',
                ],
                $params
            )),
        ]);

        return $next($params, $payload);
    }
}
