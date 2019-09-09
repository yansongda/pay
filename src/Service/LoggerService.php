<?php

namespace Yansongda\Pay\Service;

use Pimple\Container;
use Yansongda\Pay\Contract\ServiceInterface;
use Yansongda\Supports\Logger;

class LoggerService implements ServiceInterface
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
        $pimple['logger'] = $pimple['log'] = function ($container) {
            /* @var \Yansongda\Pay\Pay $container */
            $logger = new Logger();

            $config = ['identify' => 'yansongda.pay'];

            if (isset($container['config']['log'])) {
                $config = array_merge(
                    $config,
                    $container['config']['log']
                );
            }

            $logger->setConfig($config);

            return $logger;
        };
    }
}
