<?php

namespace Yansongda\Pay\Service;

use GuzzleHttp\Client;
use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Contract\HttpInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;

class HttpServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function prepare(array $data): void
    {
        Pay::set(HttpInterface::class, null);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function register(Pay $pay): void
    {
        $service = function () use ($pay) {
            /* @var \Yansongda\Supports\Config $config */
            $config = Pay::get(ConfigInterface::class);

            return $pay::make(Client::class, [
                'config' => $config->get('http', []),
            ]);
        };

        $pay::set(HttpInterface::class, $service);
    }
}
