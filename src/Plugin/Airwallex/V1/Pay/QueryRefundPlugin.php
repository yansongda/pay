<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Airwallex\V1\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

class QueryRefundPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Airwallex][V1][Pay][QueryRefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $id = $payload->get('id', $payload->get('refund_id'));

        if (empty($id)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Airwallex 退款查询缺少必要参数 -- [id] or [refund_id]');
        }

        $rocket->mergePayload([
            '_method' => 'GET',
            '_url' => 'api/v1/pa/refunds/'.$id,
        ]);

        Logger::info('[Airwallex][V1][Pay][QueryRefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
