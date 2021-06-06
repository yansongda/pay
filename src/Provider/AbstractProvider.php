<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use Throwable;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Pipeline;

abstract class AbstractProvider
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     *
     * @return \Yansongda\Supports\Collection|\Psr\Http\Message\ResponseInterface
     */
    public function pay(array $plugins, array $params)
    {
        $this->verifyPlugin($plugins);

        /* @var Pipeline $pipeline */
        $pipeline = Pay::get(Pipeline::class);

        /* @var Rocket $rocket */
        $rocket = $pipeline
            ->send((new Rocket())->setParams($params)->setPayload(new Collection()))
            ->through($plugins)
            ->via('assembly')
            ->then(function ($rocket) {
                return $this->ignite($rocket);
            });

        return $rocket->getDestination();
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     */
    public function ignite(Rocket $rocket): Rocket
    {
        if (!should_http_request($rocket)) {
            return $rocket;
        }

        /* @var HttpClientInterface $http */
        $http = Pay::get(HttpClientInterface::class);

        try {
            $response = $http->sendRequest($rocket->getRadar());
        } catch (Throwable $e) {
            throw new InvalidResponseException(InvalidResponseException::REQUEST_RESPONSE_ERROR, $e->getMessage());
        }

        return $rocket->setDestination($response);
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function verifyPlugin(array $plugins): void
    {
        foreach ($plugins as $plugin) {
            if ((!is_object($plugin) && !is_callable($plugin) && !class_exists($plugin)) ||
                !in_array(PluginInterface::class, class_implements($plugin))) {
                throw new InvalidParamsException(InvalidParamsException::PLUGIN_ERROR, "[$plugin] is not incompatible");
            }
        }
    }
}
