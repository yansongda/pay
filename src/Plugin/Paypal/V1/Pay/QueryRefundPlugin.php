<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Paypal\V1\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://developer.paypal.com/docs/api/payments/v2/#refunds_get
 */
class QueryRefundPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Paypal][V1][Pay][QueryRefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $refundId = $params['refund_id'] ?? '';

        $rocket->mergePayload([
            '_method' => 'GET',
            '_url' => 'v2/payments/refunds/'.$refundId,
        ]);

        Logger::info('[Paypal][V1][Pay][QueryRefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
