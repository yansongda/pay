<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Web;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/bff76748_alipay.trade.query?pathHash=e3ddce1d&ref=api&scene=23
 */
class QueryPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][web][DetailPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][web][DetailPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
