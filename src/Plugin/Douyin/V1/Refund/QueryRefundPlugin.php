<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Douyin\V1\Refund;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

use function Yansongda\Artful\filter_params;

/**
 * 查询退款：透传 refund_id/out_refund_no/order_id（三选一）等业务字段，业务字段均无 app_id，不注入.
 */
class QueryRefundPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Douyin][V1][Refund][QueryRefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        if (is_null($payload) || filter_params($payload)->isEmpty()) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 抖音查询退款，缺少必要的业务参数');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/api/trade_basic/v1/developer/refund_query/',
        ]);

        Logger::info('[Douyin][V1][Refund][QueryRefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
