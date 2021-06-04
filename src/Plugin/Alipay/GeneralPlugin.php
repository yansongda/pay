<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Rocket;

class GeneralPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $rocket->mergePayload([
            'method' => $this->getMethod(),
            'biz_content' => $rocket->getParams(),
        ]);

        return $next($rocket);
    }

    protected function getMethod(): string
    {
        return 'alipay.trade.cancel';
    }
}
