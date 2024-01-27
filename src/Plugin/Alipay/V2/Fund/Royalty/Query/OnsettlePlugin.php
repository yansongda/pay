<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Fund\Royalty\Query;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/d87dc009_alipay.trade.order.onsettle.query?pathHash=53466049&ref=api&scene=common
 */
class OnsettlePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Fund][Royalty][Query][OnsettlePlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.order.onsettle.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Fund][Royalty][Query][OnsettlePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
