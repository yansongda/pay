<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Bestpay\V1;

use Closure;
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

use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\verify_bestpay_sign;

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
        Logger::debug('[Bestpay][V1][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $request = $this->getRequestCollection($params);

        // @phpstan-ignore-next-line
        $rocket->setParams($params['_params'] ?? []);

        $config = get_provider_config('bestpay', $rocket->getParams());

        $sign = $request->get('sign', '');
        $payload = $request->except(['sign']);

        verify_bestpay_sign($config, $payload->all(), $sign);

        $rocket->setDirection(NoHttpRequestDirection::class)
            ->setDestination($request);

        Logger::info('[Bestpay][V1][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws InvalidParamsException
     */
    protected function getRequestCollection(array $params): Collection
    {
        $request = $params['_request'] ?? null;

        if (!$request instanceof Collection) {
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID);
        }

        return $request;
    }
}
