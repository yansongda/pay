<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Paypal\V2\Pay;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\verify_paypal_webhook_sign;

class CallbackPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Paypal][V2][Pay][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->init($rocket);

        /* @phpstan-ignore-next-line */
        verify_paypal_webhook_sign($rocket->getDestinationOrigin(), $rocket->getParams());

        $body = json_decode((string) $rocket->getDestination()->getBody(), true);

        $rocket->setDirection(NoHttpRequestDirection::class)
            ->setPayload(new Collection($body))
            ->setDestination(new Collection($body));

        Logger::info('[Paypal][V2][Pay][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws InvalidParamsException
     */
    protected function init(Rocket $rocket): void
    {
        $request = $rocket->getParams()['_request'] ?? null;
        $params = $rocket->getParams()['_params'] ?? [];

        if (!$request instanceof ServerRequestInterface) {
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, '参数异常: PayPal 回调参数不正确');
        }

        $rocket->setDestination(clone $request)
            ->setDestinationOrigin($request)
            ->setParams($params);
    }
}
