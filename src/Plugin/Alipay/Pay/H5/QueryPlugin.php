<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\H5;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/4e2d51d1_alipay.trade.query?pathHash=8abc6ffe&ref=api&scene=common
 */
class QueryPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Pay][Wap][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Pay][Wap][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
