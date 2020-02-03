<?php

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Contract\ServiceInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Logger;

class LoggerServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
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
