<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Pos;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/e84f0d79_alipay.trade.close?pathHash=b25c3fc7&ref=api&scene=common
 */
class ClosePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][pos][ClosePlugin] 通用插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.close',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][pos][ClosePlugin] 通用插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
