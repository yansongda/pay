<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Ebpp;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Rocket;

class PdeductSignCancelPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $rocket->mergePayload([
            'method' => 'alipay.ebpp.pdeduct.sign.cancel',
            'biz_content' => array_merge(
                [
                    'agent_channel' => 'PUBLICPLATFORM',
                ],
                $rocket->getParams(),
            ),
        ]);

        return $next($rocket);
    }
}
