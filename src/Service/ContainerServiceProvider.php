<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Closure;
use Hyperf\Pimple\ContainerFactory as DefaultContainer;
use Hyperf\Utils\ApplicationContext as HyperfContainer;
use Illuminate\Container\Container as LaravelContainer;
use Psr\Container\ContainerInterface;
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
    private array $detectApplication = [
        'laravel' => LaravelContainer::class,
        'hyperf' => HyperfContainer::class,
    ];

    /**
     * @param mixed $data
     *
     * @throws ContainerException
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
     * @throws ContainerException
     * @throws ContainerNotFoundException
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
     * @throws ContainerException
     * @throws ContainerNotFoundException
     */
    protected function hyperfApplication(): bool
    {
        if (!HyperfContainer::hasContainer()) {
            return false;
        }

        Pay::setContainer(static fn () => HyperfContainer::getContainer());

        Pay::set(\Yansongda\Pay\Contract\ContainerInterface::class, HyperfContainer::getContainer());

        if (!Pay::has(ContainerInterface::class)) {
            Pay::set(ContainerInterface::class, HyperfContainer::getContainer());
        }

        return true;
    }

    /**
     * @throws ContainerNotFoundException
     */
    protected function defaultApplication(): void
    {
        if (!class_exists(DefaultContainer::class)) {
            throw new ContainerNotFoundException('Init failed! Maybe you should install `hyperf/pimple` first', Exception::CONTAINER_NOT_FOUND);
        }

        $container = (new DefaultContainer())();

        Pay::setContainer($container);
    }
}
