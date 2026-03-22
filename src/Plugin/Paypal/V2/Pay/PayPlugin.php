<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Paypal\V2\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

use function Yansongda\Pay\get_provider_config;

/**
 * @see https://developer.paypal.com/docs/api/orders/v2/#orders_create
 */
class PayPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Paypal][V2][Pay][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();
        $config = get_provider_config('paypal', $params);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => 'v2/checkout/orders',
            'intent' => $payload->get('intent', 'CAPTURE'),
            'purchase_units' => $payload->get('purchase_units', []),
            'application_context' => array_filter([
                'return_url' => $payload->get('return_url') ?? $config['return_url'] ?? null,
                'cancel_url' => $payload->get('cancel_url') ?? $config['cancel_url'] ?? null,
                'brand_name' => $payload->get('brand_name') ?? $config['brand_name'] ?? null,
                'landing_page' => $payload->get('landing_page') ?? null,
                'user_action' => $payload->get('user_action', 'PAY_NOW'),
            ]),
        ]);

        Logger::info('[Paypal][V2][Pay][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
