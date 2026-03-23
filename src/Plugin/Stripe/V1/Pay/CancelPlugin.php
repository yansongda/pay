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
 * @see https://stripe.com/docs/api/payment_intents/cancel
 */
class CancelPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Stripe][V1][Pay][CancelPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $paymentIntentId = $params['payment_intent_id'] ?? '';

        if (empty($paymentIntentId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Stripe 取消 PaymentIntent，缺少 payment_intent_id 参数');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => 'v1/payment_intents/'.$paymentIntentId.'/cancel',
        ]);

        Logger::info('[Stripe][V1][Pay][CancelPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
