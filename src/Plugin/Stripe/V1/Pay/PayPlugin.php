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
 * @see https://stripe.com/docs/api/payment_intents/create
 */
class PayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Stripe][V1][Pay][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        $amount = $payload->get('amount');
        $currency = $payload->get('currency');

        if (empty($amount) || empty($currency)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Stripe 创建 PaymentIntent，缺少 amount 或 currency 参数');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/v1/payment_intents',
            'amount' => $amount,
            'currency' => $currency,
        ]);

        Logger::info('[Stripe][V1][Pay][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
