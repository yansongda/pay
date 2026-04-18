<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Airwallex\V1\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Traits\AirwallexTrait;

/**
 * @see https://www.airwallex.com/docs/api/payments/refunds/create
 */
class RefundPlugin implements PluginInterface
{
    use AirwallexTrait;

    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Airwallex][V1][Pay][RefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $paymentIntentId = $payload->get('payment_intent_id', $payload->get('id'));

        if (empty($paymentIntentId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Airwallex 退款缺少必要参数 -- [payment_intent_id] or [id]');
        }

        $rocket->mergePayload(array_filter([
            '_method' => 'POST',
            '_url' => 'api/v1/pa/refunds/create',
            'request_id' => $payload->get('request_id', self::getAirwallexRequestId()),
            'payment_intent_id' => $paymentIntentId,
            'amount' => $payload->get('amount'),
            'reason' => $payload->get('reason'),
            'metadata' => $payload->get('metadata'),
        ], static fn ($value) => !is_null($value)));

        Logger::info('[Airwallex][V1][Pay][RefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
