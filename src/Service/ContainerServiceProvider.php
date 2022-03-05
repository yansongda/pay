<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Closure;
use DI\ContainerBuilder;
use Hyperf\Utils\ApplicationContext as HyperfApplication;
use Illuminate\Container\Container as LaravelContainer;
use Psr\Container\ContainerInterface;
use think\Container as ThinkContainer;
use Throwable;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ContainerNotFoundException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class ContainerServiceProvider implements ServiceProviderInterface
{
    private $detectApplication = [
        'laravel' => LaravelContainer::class,
        'think' => ThinkContainer::class,
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

        foreach ($this->detectApplication as $framework => $application) {
            if (class_exists($application) && $this->{$framework.'Application'}()) {
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
        Pay::setContainer(static function () {
            return LaravelContainer::getInstance();
        });

        if (!Pay::has(ContainerInterface::class)) {
            Pay::set(ContainerInterface::class, LaravelContainer::getInstance());
        }

        return true;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     */
    protected function thinkApplication(): bool
    {
        Pay::setContainer(static function () {
            return ThinkContainer::getInstance();
        });

        if (!Pay::has(ContainerInterface::class)) {
            Pay::set(ContainerInterface::class, ThinkContainer::getInstance());
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

        Pay::setContainer(static function () {
            return HyperfApplication::getContainer();
        });

        if (!Pay::has(ContainerInterface::class)) {
            Pay::set(ContainerInterface::class, HyperfApplication::getInstance());
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
