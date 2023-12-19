<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund\Royalty;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/9ef980b7_alipay.trade.order.settle.query?pathHash=688b1c13&ref=api&scene=common
 */
class QuerySettlePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][royalty][QuerySettlePlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.order.settle.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][royalty][QuerySettlePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
