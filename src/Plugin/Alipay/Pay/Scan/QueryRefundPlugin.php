<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Scan;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/02ekfl?pathHash=a223936b&ref=api&scene=common
 */
class QueryRefundPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][scan][QueryRefundPlugin] 通用插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.fastpay.refund.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][scan][QueryRefundPlugin] 通用插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
