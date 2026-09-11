<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Bestpay\V1\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://mapi.bestpay.com.cn/gapi/telecomPortal/getApiDocument?productCode=1008 超级收银台 /pay/closeOrder
 */
class ClosePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Bestpay][V1][Pay][ClosePlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_path' => '/pay/closeOrder',
        ]);

        Logger::info('[Bestpay][V1][Pay][ClosePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
