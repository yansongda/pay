<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Combine;

use Yansongda\Pay\Rocket;

class QueryPlugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\QueryPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        $params = $rocket->getParams();

        return 'v3/combine-transactions/out-trade-no/'.
            ($params['combine_out_trade_no'] ?? $params['out_trade_no'] ?? '');
    }
}
