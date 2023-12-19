<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund\Royalty;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/d87dc009_alipay.trade.order.onsettle.query?pathHash=53466049&ref=api&scene=common
 */
class QueryOnsettlePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][royalty][QueryOnsettlePlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.order.onsettle.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][royalty][QueryOnsettlePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
