<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Direction\NoHttpRequestDirection;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\filter_params;
use function Yansongda\Pay\get_unipay_config;
use function Yansongda\Pay\verify_unipay_sign;

class CallbackPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_unipay_config($params);

        $rocket->setPayload($params);

        $collection = Collection::wrap(filter_params($params))->except('signature')->sortKeys();

        verify_unipay_sign($config, $collection->toString(), $params['signature'] ?? '', $params['signPubKeyCert'] ?? null);

        $rocket->setDirection(NoHttpRequestDirection::class)
            ->setDestination($rocket->getPayload());

        Logger::info('[Unipay][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
