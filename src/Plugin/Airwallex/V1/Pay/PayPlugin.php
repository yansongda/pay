<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Airwallex\V1\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AirwallexConfig;
use Yansongda\Pay\Traits\AirwallexTrait;

/**
 * @see https://www.airwallex.com/docs/api/payments/payment_intents/create
 */
class PayPlugin implements PluginInterface
{
    use AirwallexTrait;

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Airwallex][V1][Pay][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();

        /** @var AirwallexConfig $config */
        $config = self::getProviderConfig('airwallex', $params);

        $rocket->mergePayload(array_filter([
            '_method' => 'POST',
            '_url' => 'api/v1/pa/payment_intents/create',
            'request_id' => $payload->get('request_id', self::getAirwallexRequestId()),
            'amount' => $payload->get('amount'),
            'currency' => $payload->get('currency'),
            'merchant_order_id' => $payload->get('merchant_order_id'),
            'return_url' => $payload->get('return_url') ?? $config->getReturnUrl(),
            'payment_method' => $payload->get('payment_method'),
            'payment_method_options' => $payload->get('payment_method_options'),
            'customer_id' => $payload->get('customer_id'),
            'customer' => $payload->get('customer'),
            'descriptor' => $payload->get('descriptor'),
            'order' => $payload->get('order'),
            'metadata' => $payload->get('metadata'),
        ], static fn ($value) => !is_null($value)));

        Logger::info('[Airwallex][V1][Pay][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
