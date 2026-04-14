<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Airwallex\V1\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

use function Yansongda\Pay\get_airwallex_request_id;

class ConfirmPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Airwallex][V1][Pay][ConfirmPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $paymentIntentId = $payload?->get('payment_intent_id', $payload?->get('id'));

        if (empty($paymentIntentId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Airwallex 确认支付缺少必要参数 -- [payment_intent_id] or [id]');
        }

        $rocket->mergePayload(array_filter([
            '_method' => 'POST',
            '_url' => 'api/v1/pa/payment_intents/'.$paymentIntentId.'/confirm',
            'request_id' => $payload->get('request_id', get_airwallex_request_id()),
            'payment_method' => $payload->get('payment_method'),
            'payment_method_options' => $payload->get('payment_method_options'),
            'customer_id' => $payload->get('customer_id'),
            'customer' => $payload->get('customer'),
            'return_url' => $payload->get('return_url'),
            'payment_consent_id' => $payload->get('payment_consent_id'),
            'merchant_trigger_reason' => $payload->get('merchant_trigger_reason'),
        ], static fn ($value) => !is_null($value)));

        Logger::info('[Airwallex][V1][Pay][ConfirmPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
