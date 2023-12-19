<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Authorization;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/064jhi?pathHash=7435c8fd&ref=api&scene=common
 */
class UnfreezePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][pay][authorization][AppFreezePlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.fund.auth.order.unfreeze',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][pay][authorization][AppFreezePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
