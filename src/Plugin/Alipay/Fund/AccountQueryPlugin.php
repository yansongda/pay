<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund;

use Closure;
use Yansongda\Supports\Collection;

class AccountQueryPlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->merge([
            'method' => 'alipay.fund.account.query',
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
