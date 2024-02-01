<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Auth;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/064jhh?pathHash=1cfd43b0&ref=api&scene=common
 */
class CancelPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Pay][Authorization][Auth][CancelPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.fund.auth.operation.cancel',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Pay][Authorization][Auth][CancelPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
