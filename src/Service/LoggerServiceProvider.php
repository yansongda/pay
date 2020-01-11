<?php

namespace Yansongda\Pay\Service;

use Pimple\Container;
use Yansongda\Pay\Contract\ServiceInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Logger;

class LoggerServiceProvider implements ServiceProviderInterface
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
            $logger = new class($container) extends Logger implements ServiceInterface {
                /**
                 * container.
                 *
                 * @var \Yansongda\Pay\Pay
                 */
                protected $container;

                /**
                 * Bootstrap.
                 */
                public function __construct(Pay $container)
                {
                    $this->container = $container;
                }

                public function __call($method, $args): bool
                {
                    if (false === $this->container['config']->get('log.enable', false)) {
                        return true;
                    }

                    return parent::__call($method, $args);
                }
            };

            $logger->setConfig($container['config']['log']);

            return $logger;
        };
    }
}
