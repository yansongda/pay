<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\App;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/6c0cdd7d_alipay.trade.refund?pathHash=4081e89c&ref=api&scene=common
 */
class RefundPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][app][RefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.refund',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][app][RefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
