<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use GuzzleHttp\Client;
use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Config;

class HttpServiceProvider implements ServiceProviderInterface
{
    /**
     * @param mixed $data
     *
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function register($data = null): void
    {
        /* @var Config $config */
        $config = Pay::get(ConfigInterface::class);

        if (class_exists(Client::class)) {
            $service = new Client($config->get('http', []));

            Pay::set(HttpClientInterface::class, $service);
        }
    }
}
