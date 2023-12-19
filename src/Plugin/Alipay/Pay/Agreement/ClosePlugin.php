<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Agreement;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/24a75394_alipay.trade.close?pathHash=7bf94ec4&ref=api&scene=common
 */
class ClosePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][pay][agreement][ClosePlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.close',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][pay][agreement][ClosePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
