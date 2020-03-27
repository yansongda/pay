<?php

namespace Yansongda\Pay\Service;

use Symfony\Component\EventDispatcher\EventDispatcher;
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
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    public function register(Pay $pay): void
    {
        $service = function () {
            $event = new class() extends EventDispatcher {
            };

            $event->addSubscriber(new KernelLogSubscriber());

            return $event;
        };

        $pay::set('event', $service);
    }
}
