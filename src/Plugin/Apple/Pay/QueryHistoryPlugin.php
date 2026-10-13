<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Apple\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

/**
 * @see https://developer.apple.com/documentation/appstoreserverapi/get-transaction-history
 */
class QueryHistoryPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Apple][Pay][QueryHistoryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $id = $payload?->get('transaction_id') ?? '';

        if (empty($id)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Apple 查询交易历史，缺少 transaction_id 参数');
        }

        $rocket->mergePayload([
            '_method' => 'GET',
            '_url' => '/inApps/v2/history/'.urlencode($id),
        ]);

        // transaction_id 已拼入 `_url`，需要从 payload 中剔除，避免残留在最终请求 query string 中被 Apple 拒绝
        $rocket->exceptPayload('transaction_id');

        Logger::info('[Apple][Pay][QueryHistoryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
