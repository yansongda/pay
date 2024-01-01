<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Direction\NoHttpRequestDirection;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\filter_params;
use function Yansongda\Pay\get_alipay_config;
use function Yansongda\Pay\verify_alipay_sign;

class CallbackPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     * @throws InvalidSignException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_alipay_config($params);

        $rocket->setPayload(filter_params($params, fn ($k, $v) => '' !== $v && 'sign' != $k && 'sign_type' != $k));

        verify_alipay_sign($config, $rocket->getPayload()->sortKeys()->toString(), $params['sign'] ?? '');

        $rocket->setDirection(NoHttpRequestDirection::class)
            ->setDestination($rocket->getPayload());

        Logger::info('[Alipay][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
