<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

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
    public function register(?array $data = null): void
    {
        foreach ($this->detectApplication as $application) {
            if (class_exists($application) && $this->{$application.'Application'}()) {
                return;
            }
        }

        $this->defaultApplication();
    }

    protected function laravelApplication(): bool
    {
        Pay::setContainer(function () {
            return LaravelContainer::getInstance();
        });

        return true;
    }

    protected function thinkApplication(): bool
    {
        Pay::setContainer(function () {
            return ThinkContainer::getInstance();
        });

        return true;
    }

    protected function hyperfApplication(): bool
    {
        if (!HyperfApplication::hasContainer()) {
            return false;
        }

        Pay::setContainer(function () {
            return HyperfApplication::getContainer();
        });

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
