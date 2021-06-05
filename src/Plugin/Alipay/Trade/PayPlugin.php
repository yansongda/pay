<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Trade;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Rocket;

class PayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $rocket->mergePayload([
            'method' => 'alipay.trade.pay',
            'biz_content' => array_merge(
                [
                    'product_code' => 'FACE_TO_FACE_PAYMENT',
                    'scene' => 'bar_code',
                ],
                $rocket->getParams(),
            ),
        ]);

        return $next($rocket);
    }
}
