<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Qra;

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
use function Yansongda\Pay\verify_unipay_sign_qra;

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
        Logger::debug('[Unipay][Qra][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_unipay_config($params);
        $destination = filter_params($params);

        if (isset($params['status']) && 0 == $params['status']) {
            verify_unipay_sign_qra($config, $destination);
        }

        $rocket->setPayload($params)
            ->setDirection(NoHttpRequestDirection::class)
            ->setDestination(new Collection($destination));

        Logger::info('[Unipay][Qra][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
