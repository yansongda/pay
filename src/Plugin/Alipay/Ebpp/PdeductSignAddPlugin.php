<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Ebpp;

use Closure;
use Yansongda\Supports\Collection;

class PdeductSignAddPlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->merge([
            'method' => 'alipay.ebpp.pdeduct.sign.add',
            'biz_content' => json_encode(array_merge(
                [
                    'charge_inst' => 'CQCENTERELECTRIC',
                    'agent_channel' => 'PUBLICPLATFORM',
                    'deduct_prod_code' => 'INST_DIRECT_DEDUCT',
                ],
                $params
            )),
        ]);

        return $next($params, $payload);
    }
}
