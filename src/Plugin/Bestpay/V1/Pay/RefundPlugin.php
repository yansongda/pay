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
 * @see https://render.bestpay.cn/open-developers/index.html#/documentCenterLayout/apiDetail?apiPath=/integrate/refund&productId=1008 翼支付官方文档（接口详情）
 */
class RefundPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Bestpay][V1][Pay][RefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_url' => '/integrate/refund',
        ]);

        Logger::info('[Bestpay][V1][Pay][RefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
