<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Yansongda\Pay\Contract\EventDispatcherInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
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
        $event = $this->getDefaultDispatcher();

        if (class_exists(EventDispatcher::class)) {
            $event = Pay::get(EventDispatcher::class);
        }

        Pay::set(EventDispatcherInterface::class, $event);
    }

    protected function getDefaultDispatcher(): EventDispatcherInterface
    {
        return new class() implements EventDispatcherInterface {
            /**
             * Adds an event listener that listens on the specified events.
             *
             * @param callable $listener The listener
             * @param int      $priority The higher this value, the earlier an event
             *                           listener will be triggered in the chain (defaults to 0)
             */
            public function addListener(string $eventName, $listener, int $priority = 0)
            {
            }

            /**
             * Adds an event subscriber.
             *
             * The subscriber is asked for all the events it is
             * interested in and added as a listener for these events.
             */
            public function addSubscriber(EventSubscriberInterface $subscriber)
            {
            }

            /**
             * Removes an event listener from the specified events.
             *
             * @param callable $listener The listener to remove
             */
            public function removeListener(string $eventName, $listener)
            {
            }

            /**
             * @author yansongda <me@yansongda.cn>
             */
            public function removeSubscriber(EventSubscriberInterface $subscriber)
            {
            }

            /**
             * Gets the listeners of a specific event or all listeners sorted by descending priority.
             *
             * @return array The event listeners for the specified event, or all event listeners by event name
             */
            public function getListeners(string $eventName = null): array
            {
                return [];
            }

            public function dispatch(object $event, string $eventName = null): object
            {
                return $event;
            }

            /**
             * Gets the listener priority for a specific event.
             *
             * Returns null if the event or the listener does not exist.
             *
             * @param callable $listener The listener
             *
             * @return int|null The event listener priority
             */
            public function getListenerPriority(string $eventName, $listener): ?int
            {
                return null;
            }

            /**
             * Checks whether an event has any registered listeners.
             *
             * @return bool true if the specified event has any listeners, false otherwise
             */
            public function hasListeners(string $eventName = null): bool
            {
                return false;
            }
        };
    }
}
