<?php

namespace Yansongda\Pay\Service;

use Pimple\Container;
use Yansongda\Pay\Contract\ServiceInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Supports\Config;

class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple['config'] = function ($container) {
            /* @var \Yansongda\Pay\Pay $container */
            return new class($container->getConfig()) extends Config implements ServiceInterface {
            };
        };
    }
}
