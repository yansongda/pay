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

        $payload = $rocket->getPayload();

        if (!$payload?->get('_native_api', false)) {
            $this->normalizeDestination($rocket);

            Logger::info('[Airwallex][V1][Pay][PayConfirmPlugin] 未开启 Native API，跳过 confirm', ['rocket' => $rocket]);

            return $rocket;
        }

        $destination = $rocket->getDestination();

        if (!$destination instanceof Collection) {
            return $rocket;
        }

        $confirmParams = array_merge($rocket->getParams(), [
            'payment_intent_id' => $destination->get('id'),
        ]);

        $confirmPayload = array_merge($payload instanceof Collection ? $payload->all() : [], [
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

        $this->normalizeDestination($rocket);

        Logger::info('[Airwallex][V1][Pay][PayConfirmPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    protected function normalizeDestination(Rocket $rocket): void
    {
        $destination = $rocket->getDestination();

        if (!$destination instanceof Collection) {
            return;
        }

        $nextActionType = strval($destination->get('next_action.type', ''));
        $payUrl = $this->getPayUrl($destination);

        $destination->put('payment_intent_id', $destination->get('id'));
        $destination->put('next_action_type', $nextActionType);
        $destination->put('pay_url', $payUrl);
    }

    protected function getPayUrl(Collection $destination): string
    {
        $nextActionType = strval($destination->get('next_action.type', ''));

        return match ($nextActionType) {
            'render_qrcode' => strval($destination->get('next_action.qrcode_url', $destination->get('next_action.url', ''))),
            'redirect' => strval($destination->get('next_action.url', '')),
            default => '',
        };
    }
}
