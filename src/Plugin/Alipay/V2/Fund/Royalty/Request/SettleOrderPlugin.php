<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Fund\Royalty\Request;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/c3b24498_alipay.trade.order.settle?pathHash=8e6acab4&ref=api&scene=common
 */
class SettleOrderPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Fund][Royalty][Request][SettleOrderPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.order.settle',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Fund][Royalty][Request][SettleOrderPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
