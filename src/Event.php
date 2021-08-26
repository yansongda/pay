<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Yansongda\Pay\Contract\EventDispatcherInterface;
use Yansongda\Pay\Exception\InvalidConfigException;

/**
 * @method static Event\Event dispatch(object $event)
 */
class Event
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    public static function __callStatic(string $method, array $args): void
    {
        if (!Pay::hasContainer() || !Pay::has(EventDispatcherInterface::class)) {
            return;
        }

        $class = Pay::get(EventDispatcherInterface::class);

        if ($class instanceof \Psr\EventDispatcher\EventDispatcherInterface) {
            $class->{$method}(...$args);

            return;
        }

        throw new InvalidConfigException(Exception\Exception::EVENT_CONFIG_ERROR);
    }
}
