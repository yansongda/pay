<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Paypal\V2\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Supports\Collection;

class CallbackPlugin implements PluginInterface
{
    /**
     * @todo 当前实现未验证 PayPal Webhook 签名，建议生产环境中通过 PayPal 的
     *       verify-webhook-signature API 进行验证以确保回调的真实性。
     *
     * @throws ContainerException
     * @throws InvalidResponseException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Paypal][V2][Pay][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        if (empty($params)) {
            throw new InvalidResponseException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, 'PayPal 回调参数为空');
        }

        $rocket->setPayload(Collection::wrap($params))
            ->setDirection(NoHttpRequestDirection::class)
            ->setDestination($rocket->getPayload());

        Logger::info('[Paypal][V2][Pay][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
