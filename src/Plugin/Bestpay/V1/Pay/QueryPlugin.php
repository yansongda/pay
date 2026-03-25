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
 * @see https://render.bestpay.cn/open-developers/index.html#/documentCenterLayout/accessProcess
 */
class QueryPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Bestpay][V1][Pay][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_url' => 'pay/queryPayOrder',
            '_method' => 'POST',
        ]);

        Logger::info('[Bestpay][V1][Pay][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
