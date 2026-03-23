<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Stripe\V1\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

/**
 * @see https://stripe.com/docs/api/refunds/retrieve
 */
class QueryRefundPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Stripe][V1][Pay][QueryRefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $refundId = $params['refund_id'] ?? '';

        if (empty($refundId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Stripe 查询退款，缺少 refund_id 参数');
        }

        $rocket->mergePayload([
            '_method' => 'GET',
            '_url' => 'v1/refunds/'.$refundId,
        ]);

        Logger::info('[Stripe][V1][Pay][QueryRefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
