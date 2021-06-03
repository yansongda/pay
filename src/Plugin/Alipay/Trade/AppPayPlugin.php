<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Trade;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Packer\ResponsePacker;
use Yansongda\Pay\Rocket;

class AppPayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $rocket->setDirection(ResponsePacker::class)
            ->mergePayload([
            'method' => 'alipay.trade.app.pay',
            'biz_content' => array_merge(
                ['product_code' => 'QUICK_MSECURITY_PAY'],
                $rocket->getParams(),
            ),
        ]);

        return $next($rocket);
    }
}
