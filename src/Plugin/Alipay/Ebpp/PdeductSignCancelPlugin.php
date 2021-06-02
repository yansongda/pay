<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Ebpp;

use Closure;
use Yansongda\Supports\Collection;

class PdeductSignCancelPlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->merge([
            'method' => 'alipay.ebpp.pdeduct.sign.cancel',
            'biz_content' => json_encode(array_merge(
                [
                    'agent_channel' => 'PUBLICPLATFORM',
                ],
                $params
            )),
        ]);

        return $next($params, $payload);
    }
}
