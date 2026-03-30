<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3\Fund\Transfer\Fund;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open-v3/02a68g
 */
class QueryPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][Fund][Transfer][Fund][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_method' => 'GET',
            '_url' => '/v3/alipay/fund/trans/common/query?'.http_build_query($rocket->getParams()),
            '_body' => '',
        ]);

        Logger::info('[Alipay][V3][Fund][Transfer][Fund][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
