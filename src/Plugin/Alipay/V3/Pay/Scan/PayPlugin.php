<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3\Pay\Scan;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open-v3/02s4f0
 */
class PayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][Pay][Scan][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/v3/alipay/trade/precreate',
            '_body' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][V3][Pay][Scan][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
