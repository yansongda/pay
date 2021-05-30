<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Trade;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Rocket;

class PagePayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        return $next(
            $rocket->setType(Response::class)
                ->mergePayload([
                'method' => 'alipay.trade.page.pay',
                'biz_content' => array_merge(
                    ['product_code' => 'FAST_INSTANT_TRADE_PAY'],
                    $rocket->getParams()
                ),
            ])
        );
    }
}
