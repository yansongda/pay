<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Fund\Royalty\Query;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/6f314ee9_alipay.trade.royalty.rate.query?pathHash=9118088a&ref=api&scene=common
 */
class RatePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Fund][Royalty][Query][RatePlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.royalty.rate.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Fund][Royalty][Query][RatePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
