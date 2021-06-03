<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Trade;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Rocket;

class CreatePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $rocket->mergePayload([
            'method' => 'alipay.trade.create',
            'biz_content' => $rocket->getParams(),
        ]);

        return $next($rocket);
    }
}
