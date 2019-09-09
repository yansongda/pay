<?php

namespace Yansongda\Pay\Service;

use Pimple\Container;
use Yansongda\Pay\Contract\ServiceInterface;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;

class ConfigService implements ServiceInterface
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
            return new Config($container->getConfig());
        };
    }
}
