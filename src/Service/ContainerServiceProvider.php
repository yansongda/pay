<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Closure;
use DI\ContainerBuilder;
use Hyperf\Utils\ApplicationContext as HyperfApplication;
use Illuminate\Container\Container as LaravelContainer;
use Psr\Container\ContainerInterface;
use Throwable;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ContainerNotFoundException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

/**
 * @codeCoverageIgnore
 */
class ContainerServiceProvider implements ServiceProviderInterface
{
    private $detectApplication = [
        'laravel' => LaravelContainer::class,
        'hyperf' => HyperfApplication::class,
    ];

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    public function register($data = null): void
    {
        if ($data instanceof ContainerInterface || $data instanceof Closure) {
            Pay::setContainer($data);

            return;
        }

        if (Pay::hasContainer()) {
            return;
        }

        foreach ($this->detectApplication as $framework => $application) {
            $method = $framework.'Application';

            if (class_exists($application) && method_exists($this, $method) && $this->{$method}()) {
                return;
            }
        }

        $this->defaultApplication();
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     */
    protected function laravelApplication(): bool
    {
        Pay::setContainer(static fn () => LaravelContainer::getInstance());

        Pay::set(\Yansongda\Pay\Contract\ContainerInterface::class, LaravelContainer::getInstance());

        if (!Pay::has(ContainerInterface::class)) {
            Pay::set(ContainerInterface::class, LaravelContainer::getInstance());
        }

        return true;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     */
    protected function hyperfApplication(): bool
    {
        if (!HyperfApplication::hasContainer()) {
            return false;
        }

        Pay::setContainer(static fn () => HyperfApplication::getContainer());

        Pay::set(\Yansongda\Pay\Contract\ContainerInterface::class, HyperfApplication::getContainer());

        if (!Pay::has(ContainerInterface::class)) {
            Pay::set(ContainerInterface::class, HyperfApplication::getContainer());
        }

        return true;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    protected function defaultApplication(): void
    {
        if (!class_exists(ContainerBuilder::class)) {
            throw new ContainerNotFoundException('Init failed! Maybe you should install `php-di/php-di` first', Exception::CONTAINER_NOT_FOUND);
        }

        $builder = new ContainerBuilder();

        try {
            $container = $builder->build();
            $container->set(ContainerInterface::class, $container);
            $container->set(\Yansongda\Pay\Contract\ContainerInterface::class, $container);

            Pay::setContainer($container);
        } catch (Throwable $e) {
            throw new ContainerException($e->getMessage());
        }
    }
}
