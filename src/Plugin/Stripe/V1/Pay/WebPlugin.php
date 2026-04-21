<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Stripe\V1\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\StripeConfig;
use Yansongda\Pay\Traits\StripeTrait;

/**
 * @see https://stripe.com/docs/api/checkout/sessions/create
 */
class WebPlugin implements PluginInterface
{
    use StripeTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Stripe][V1][Pay][WebPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();

        /** @var StripeConfig $config */
        $config = self::getProviderConfig('stripe', $params);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => 'v1/checkout/sessions',
            'mode' => $payload->get('mode', 'payment'),
            'success_url' => $payload->get('success_url') ?? $config->getSuccessUrl(),
            'cancel_url' => $payload->get('cancel_url') ?? $config->getCancelUrl(),
        ]);

        Logger::info('[Stripe][V1][Pay][WebPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
