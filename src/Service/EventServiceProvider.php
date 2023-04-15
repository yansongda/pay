<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Yansongda\Pay\Contract\EventDispatcherInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Pay;

class EventServiceProvider implements ServiceProviderInterface
{
    /**
     * @param mixed $data
     *
     * @throws ContainerException
     */
    public function register($data = null): void
    {
        if (class_exists(EventDispatcher::class)) {
            Pay::set(EventDispatcherInterface::class, new EventDispatcher());
        }
    }
}
