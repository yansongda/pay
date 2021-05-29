<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Ebpp;

use Closure;
use Yansongda\Supports\Collection;

class PdeductPayPlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->merge([
            'method' => 'alipay.ebpp.pdeduct.pay',
            'biz_content' => json_encode(array_merge(
                [
                    'agent_channel' => 'PUBLICFORM',
                ],
                $params
            )),
        ]);

        return $next($params, $payload);
    }
}
