<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Pos;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/6f534d7f_alipay.trade.query?pathHash=98c03720&ref=api&scene=23
 */
class QueryPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][pos][QueryPlugin] 通用插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][pos][QueryPlugin] 通用插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
