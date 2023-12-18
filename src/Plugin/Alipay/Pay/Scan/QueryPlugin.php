<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Scan;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/02ekfh?pathHash=925e7dfc&ref=api&scene=23
 */
class QueryPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][scan][DetailPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][scan][DetailPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
