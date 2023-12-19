<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund\Royalty;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/6f314ee9_alipay.trade.royalty.rate.query?pathHash=9118088a&ref=api&scene=common
 */
class QueryRatePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][royalty][QueryRatePlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.royalty.rate.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][royalty][QueryRatePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}