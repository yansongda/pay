<?php

namespace Yansongda\Pay\Service;

use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Yansongda\Pay\Contract\ServiceInterface;
use Yansongda\Pay\Listener\KernelLogSubscriber;

class EventService implements ServiceInterface
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
        $pimple['event'] = function ($container) {
            $event = new EventDispatcher();

            $event->addSubscriber(new KernelLogSubscriber($container));

            return $event;
        };
    }
}
