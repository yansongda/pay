<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Authorization;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/2c9c00e8_alipay.trade.orderinfo.sync?pathHash=bc74615a&ref=api&scene=common
 */
class SyncPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][pay][authorization][SyncPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.orderinfo.sync',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][pay][authorization][SyncPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
