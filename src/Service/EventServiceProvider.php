<?php

declare(strict_types=1);

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
     */
    public function register(Pay $pay): void
    {
        $service = function () use ($pay) {
            $event = $pay::make(EventDispatcher::class);

            $event->addSubscriber(new KernelLogSubscriber());

            return $event;
        };

        $pay::set(EventDispatcherInterface::class, $service);
    }
}
