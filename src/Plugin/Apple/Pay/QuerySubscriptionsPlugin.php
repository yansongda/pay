<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Apple\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Enum\Apple\SubscriptionStatus;
use Yansongda\Pay\Exception\Exception;

/**
 * @see https://developer.apple.com/documentation/appstoreserverapi/get-all-subscription-statuses
 */
class QuerySubscriptionsPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Apple][Pay][QuerySubscriptionsPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $id = $payload?->get('transaction_id') ?? '';

        if (empty($id)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Apple 查询订阅状态，缺少 transaction_id 参数');
        }

        // 默认 active + billing grace period（与官方文档示例一致）
        $statuses = $payload->get('_status') ?? [SubscriptionStatus::ACTIVE, SubscriptionStatus::BILLING_GRACE_PERIOD];
        if (!is_array($statuses)) {
            $statuses = [$statuses];
        }

        $statusQuery = '';
        foreach ($statuses as $status) {
            $statusEnum = $status instanceof SubscriptionStatus
                ? $status
                : (is_int($status) ? SubscriptionStatus::tryFrom($status) : null);

            if (null === $statusEnum) {
                throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Apple 查询订阅状态，_status 仅允许 SubscriptionStatus 枚举或 1-5 的整数');
            }

            $statusQuery .= 'status='.$statusEnum->value.'&';
        }
        $statusQuery = rtrim($statusQuery, '&');

        // Apple 需要重复参数 `status=1&status=4` 形式；http_build_query 对数组会生成 `status%5B0%5D=1`，故手工拼接
        $rocket->mergePayload([
            '_method' => 'GET',
            '_url' => '/inApps/v1/subscriptions/'.urlencode($id).'?'.$statusQuery,
        ]);

        // transaction_id 已拼入 `_url`，需要从 payload 中剔除，避免残留在最终请求 query string 中被 Apple 拒绝
        $rocket->exceptPayload('transaction_id');

        Logger::info('[Apple][Pay][QuerySubscriptionsPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
