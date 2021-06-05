<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Ebpp;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Rocket;

class PdeductPayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $rocket->mergePayload([
            'method' => 'alipay.ebpp.pdeduct.pay',
            'biz_content' => array_merge(
                [
                    'agent_channel' => 'PUBLICFORM',
                ],
                $rocket->getParams(),
            ),
        ]);

        return $next($rocket);
    }
}
