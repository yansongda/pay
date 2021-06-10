<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Yansongda\Pay\Contract\EventDispatcherInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Listener\KernelLogSubscriber;
use Yansongda\Pay\Pay;

class EventServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function register(Pay $pay, ?array $data = null): void
    {
        $event = new class() implements EventDispatcherInterface {
            public function addListener(string $eventName, $listener, int $priority = 0)
            {
            }

            public function addSubscriber(EventSubscriberInterface $subscriber)
            {
            }

            public function removeListener(string $eventName, $listener)
            {
            }

            public function removeSubscriber(EventSubscriberInterface $subscriber)
            {
            }

            public function getListeners(string $eventName = null)
            {
            }

            public function dispatch(object $event, string $eventName = null): object
            {
                return $event;
            }

            public function getListenerPriority(string $eventName, $listener)
            {
            }

            public function hasListeners(string $eventName = null)
            {
            }
        };

        if (class_exists(EventDispatcher::class)) {
            $event = Pay::get(EventDispatcher::class);
        }

        $event->addSubscriber(new KernelLogSubscriber());

        Pay::set(EventDispatcherInterface::class, $event);
    }
}
