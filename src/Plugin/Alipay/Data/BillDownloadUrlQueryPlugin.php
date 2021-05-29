<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Data;

use Closure;
use Yansongda\Supports\Collection;

class BillDownloadUrlQueryPlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        $payload = $payload->merge([
            'method' => 'alipay.data.dataservice.bill.downloadurl.query',
            'biz_content' => json_encode($params),
        ]);

        return $next($params, $payload);
    }
}
