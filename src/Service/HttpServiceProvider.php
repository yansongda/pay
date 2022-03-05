<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use GuzzleHttp\Client;
use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;

class HttpServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function register($data = null): void
    {
        /* @var \Yansongda\Supports\Config $config */
        $config = Pay::get(ConfigInterface::class);

        if (class_exists(Client::class)) {
            $service = new Client($config->get('http', []));

            Pay::set(HttpClientInterface::class, $service);
        }
    }
}
