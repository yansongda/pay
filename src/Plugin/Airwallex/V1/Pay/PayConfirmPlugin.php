<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Airwallex\V1\Pay;

use Closure;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Airwallex\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ObtainAccessTokenPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ResponsePlugin;
use Yansongda\Supports\Collection;

class PayConfirmPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $rocket = $next($rocket);

        Logger::debug('[Airwallex][V1][Pay][PayConfirmPlugin] 插件开始装载', ['rocket' => $rocket]);

        $destination = $rocket->getDestination();

        if (!$destination instanceof Collection) {
            return $rocket;
        }

        $confirmParams = array_merge($rocket->getParams(), [
            'payment_intent_id' => $destination->get('id'),
        ]);

        $confirmPayload = array_merge($rocket->getParams(), [
            'payment_intent_id' => $destination->get('id'),
        ]);

        $result = Artful::artful([
            StartPlugin::class,
            ObtainAccessTokenPlugin::class,
            ConfirmPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $confirmPayload);

        if ($result instanceof Collection) {
            $rocket->setParams($confirmParams)
                ->setPayload($result)
                ->setDestination($result);
        }

        Logger::info('[Airwallex][V1][Pay][PayConfirmPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
