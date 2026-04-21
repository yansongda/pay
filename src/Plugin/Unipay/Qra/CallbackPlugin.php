<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Qra;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Traits\UnipayTrait;

use function Yansongda\Artful\filter_params;

class CallbackPlugin implements PluginInterface
{
    use UnipayTrait;

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

        /** @var UnipayConfig $config */
        $config = self::getProviderConfig('unipay', $params);
        $destination = filter_params($params);

        if (isset($params['status']) && 0 == $params['status']) {
            self::verifyUnipaySignQra($config, $destination->all());
        }

        $rocket->setPayload($params)
            ->setDirection(NoHttpRequestDirection::class)
            ->setDestination($destination);

        Logger::info('[Unipay][Qra][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
