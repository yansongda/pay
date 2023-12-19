<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Mini;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/mini/05xskz?pathHash=b18b975d&ref=api&scene=common
 */
class RefundPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][pay][mini][RefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.refund',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][pay][mini][RefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
