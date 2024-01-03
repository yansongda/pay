<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Contract\HttpClientFactoryInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\HttpClientFactory;
use Yansongda\Pay\Pay;

class HttpServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws ContainerException
     */
    public function register(mixed $data = null): void
    {
        $container = Pay::getContainer();

        Pay::set(HttpClientFactoryInterface::class, new HttpClientFactory($container));
    }
}
