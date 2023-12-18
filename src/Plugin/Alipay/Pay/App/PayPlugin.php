<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\App;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/cd12c885_alipay.trade.app.pay?pathHash=c0e35284&ref=api&scene=20
 */
class PayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][app][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.app.pay',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][app][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
