<?php

namespace Yansongda\Pay\Service;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Yansongda\Pay\Contract\EventDispatcherInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Listener\KernelLogSubscriber;
use Yansongda\Pay\Pay;

class EventServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $data): void
    {
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
        $service = function () {
            $event = new EventDispatcher();

            $event->addSubscriber(new KernelLogSubscriber());

            return $event;
        };

        $pay::set(EventDispatcherInterface::class, $service);
    }
}
