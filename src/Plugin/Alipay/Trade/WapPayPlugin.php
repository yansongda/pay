<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Trade;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Rocket;

class WapPayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $rocket->setDirection(ResponseParser::class)
            ->mergePayload([
            'method' => 'alipay.trade.wap.pay',
            'biz_content' => array_merge(
                [
                    'product_code' => 'QUICK_WAP_PAY',
                ],
                $rocket->getParams(),
            ),
        ]);

        return $next($rocket);
    }
}
