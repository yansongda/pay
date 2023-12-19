<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Mini;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/mini/05xunj?pathHash=ca2a9ea6&ref=api&scene=common
 */
class CancelPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][pay][mini][CancelPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.cancel',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][pay][mini][CancelPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
