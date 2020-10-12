<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Yansongda\Pay\Contract\ContainerInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Exception\ContainerDependencyException;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ContainerNotFoundException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Service\AlipayServiceProvider;
use Yansongda\Pay\Service\ConfigServiceProvider;
use Yansongda\Pay\Service\EventServiceProvider;
use Yansongda\Pay\Service\HttpServiceProvider;
use Yansongda\Pay\Service\LoggerServiceProvider;
use Yansongda\Pay\Service\PayloadServiceProvider;
use Yansongda\Pay\Service\WechatServiceProvider;

class Pay
{
    /**
     * 普通模式.
     */
    public const MODE_NORMAL = 'normal';

    /**
     * 沙箱模式.
     */
    public const MODE_SANDBOX = 'sandbox';

    /**
     * 服务商模式.
     */
    public const MODE_SERVICE = 'service';

    /**
     * service.
     *
     * @var string[]
     */
    protected $service = [
        AlipayServiceProvider::class,
        WechatServiceProvider::class,
    ];

    /**
     * coreService.
     *
     * @var string[]
     */
    private $coreService = [
        ConfigServiceProvider::class,
        LoggerServiceProvider::class,
        EventServiceProvider::class,
        PayloadServiceProvider::class,
        HttpServiceProvider::class,
    ];

    /**
     * @var \DI\Container
     */
    private static $container;

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    private function __construct(array $config)
    {
        $this->initContainer();
        $this->registerService($config);
    }

    /**
     * __callStatic.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     *
     * @return mixed
     */
    public static function __callStatic(string $service, array $config)
    {
        self::getContainer($config);

        return self::get($service);
    }

    /**
     * get.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     *
     * @return mixed
     */
    public static function get(string $key)
    {
        try {
            return self::getContainer()->get($key);
        } catch (NotFoundException $e) {
            throw new ServiceNotFoundException($e->getMessage());
        } catch (DependencyException $e) {
            throw new ContainerDependencyException($e->getMessage());
        }
    }

    /**
     * set.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param mixed $value
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public static function set(string $key, $value): void
    {
        self::getContainer()->set($key, $value);
    }

    /**
     * has.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public static function has(string $key): bool
    {
        return self::getContainer()->has($key);
    }

    /**
     * make.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     *
     * @return mixed
     */
    public static function make()
    {
        try {
            return self::getContainer()->make(...func_get_args());
        } catch (DependencyException $e) {
            throw new ContainerDependencyException($e->getMessage());
        } catch (NotFoundException $e) {
            throw new ContainerException($e->getMessage(), [], ContainerException::CONTAINER_NOT_FOUND_ENTRY);
        }
    }

    /**
     * getContainer.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public static function getContainer(?array $initConfig = null): Container
    {
        if (self::$container instanceof Container) {
            if (isset($initConfig['cli']) && true === $initConfig['cli']) {
                self::get(self::class)->registerService($initConfig);
            }

            return self::$container;
        }

        if (is_null($initConfig)) {
            throw new ContainerNotFoundException('You must init the container first with config');
        }

        new self($initConfig);

        return self::$container;
    }

    /**
     * clear.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public static function clear(): void
    {
        self::$container = null;
    }

    /**
     * initContainer.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    private function initContainer(): void
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(false);

        try {
            self::$container = $builder->build();
        } catch (Exception $e) {
            throw new ContainerException($e->getMessage());
        }

        self::set(ContainerInterface::class, self::$container);
        self::set(Pay::class, $this);
    }

    /**
     * registerService.
     *
     * @author yansongda <me@yansongda.cn>
     */
    private function registerService(array $config): void
    {
        foreach (array_merge($this->coreService, $this->service) as $service) {
            $var = new $service();

            if ($var instanceof ServiceProviderInterface) {
                $var->prepare($config);
                $var->register($this);
            }
        }
    }
}
