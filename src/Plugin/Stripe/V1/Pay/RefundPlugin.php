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
 * @see https://stripe.com/docs/api/refunds/create
 */
class RefundPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Stripe][V1][Pay][RefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();
        $paymentIntent = $params['payment_intent'] ?? $payload->get('payment_intent') ?? '';
        $charge = $params['charge'] ?? $payload->get('charge') ?? '';

        if (empty($paymentIntent) && empty($charge)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Stripe 退款，缺少 payment_intent 或 charge 参数');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => 'v1/refunds',
        ]);

        Logger::info('[Stripe][V1][Pay][RefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
