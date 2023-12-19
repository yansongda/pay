<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Agreement;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/83a5f6d3_alipay.trade.cancel?pathHash=f27672eb&ref=api&scene=common
 */
class CancelPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][pay][agreement][CancelPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.cancel',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][pay][agreement][CancelPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
