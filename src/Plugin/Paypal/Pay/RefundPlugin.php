<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Paypal\V2\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

/**
 * @see https://developer.paypal.com/docs/api/payments/v2/#captures_refund
 */
class RefundPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Paypal][V2][Pay][RefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();
        $captureId = $params['capture_id'] ?? '';

        if (empty($captureId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: PayPal 退款，缺少 capture_id 参数');
        }

        $rocket->mergePayload(array_merge(
            ['_method' => 'POST', '_url' => 'v2/payments/captures/'.$captureId.'/refund'],
            array_filter([
                'amount' => $payload->get('amount') ?? null,
                'note_to_payer' => $payload->get('note_to_payer') ?? null,
                'invoice_id' => $payload->get('invoice_id') ?? null,
            ])
        ));

        Logger::info('[Paypal][V2][Pay][RefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
