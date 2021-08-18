<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Throwable;
use Yansongda\Pay\Contract\ContainerInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Exception\ContainerDependencyException;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ContainerNotFoundException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Service\AlipayServiceProvider;
use Yansongda\Pay\Service\ConfigServiceProvider;
use Yansongda\Pay\Service\EventServiceProvider;
use Yansongda\Pay\Service\HttpServiceProvider;
use Yansongda\Pay\Service\LoggerServiceProvider;
use Yansongda\Pay\Service\WechatServiceProvider;

/**
 * @method static Alipay alipay(array $config = [])
 * @method static Wechat wechat(array $config = [])
 */
class Pay
{
    /**
     * 正常模式.
     */
    public const MODE_NORMAL = 0;

    /**
     * 沙箱模式.
     */
    public const MODE_SANDBOX = 1;

    /**
     * 服务商模式.
     */
    public const MODE_SERVICE = 2;

    /**
     * @var string[]
     */
    protected $service = [
        AlipayServiceProvider::class,
        WechatServiceProvider::class,
    ];

    /**
     * @var string[]
     */
    private $coreService = [
        ConfigServiceProvider::class,
        LoggerServiceProvider::class,
        EventServiceProvider::class,
        HttpServiceProvider::class,
    ];

    /**
     * @var \DI\Container|null
     */
    private static $container = null;

    /**
     * Bootstrap.
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    private function __construct(array $config)
    {
        $this->initContainer();
        $this->registerServices($config);
    }

    /**
     * __callStatic.
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     *
     * @return mixed
     */
    public static function __callStatic(string $service, array $config)
    {
        if (!empty($config)) {
            self::config(...$config);
        }

        return self::get($service);
    }

    /**
     * 初始化容器、配置等信息.
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public static function config(array $config = []): Pay
    {
        if (self::hasContainer() && !($config['_force'] ?? false)) {
            return self::get(Pay::class);
        }

        return new self($config);
    }

    /**
     * 定义.
     *
     * @param mixed $value
     *
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    public static function set(string $name, $value): void
    {
        Pay::getContainer()->set($name, $value);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     *
     * @return mixed
     */
    public static function make(string $service, array $parameters = [])
    {
        try {
            return Pay::getContainer()->make(...func_get_args());
        } catch (NotFoundException $e) {
            throw new ServiceNotFoundException($e->getMessage());
        } catch (DependencyException $e) {
            throw new ContainerDependencyException($e->getMessage());
        } catch (Throwable $e) {
            throw new ContainerException($e->getMessage());
        }
    }

    /**
     * 获取服务.
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\ContainerException
     *
     * @return mixed
     */
    public static function get(string $service)
    {
        try {
            return Pay::getContainer()->get($service);
        } catch (NotFoundException $e) {
            throw new ServiceNotFoundException($e->getMessage());
        } catch (DependencyException $e) {
            throw new ContainerDependencyException($e->getMessage());
        } catch (Throwable $e) {
            throw new ContainerException($e->getMessage());
        }
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    public static function has(string $service): bool
    {
        return Pay::getContainer()->has($service);
    }

    /**
     * getContainer.
     *
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     */
    public static function getContainer(): Container
    {
        if (self::hasContainer()) {
            return self::$container;
        }

        throw new ContainerNotFoundException('You should init/config PAY first', Exception\Exception::CONTAINER_NOT_FOUND);
    }

    /**
     * has Container.
     */
    public static function hasContainer(): bool
    {
        return isset(self::$container) && self::$container instanceof Container;
    }

    /**
     * clear.
     */
    public static function clear(): void
    {
        self::$container = null;
    }

    /**
     * 注册服务.
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public static function registerService(string $service, array $config): void
    {
        $var = self::get($service);

        if ($var instanceof ServiceProviderInterface) {
            $var->register(self::get(Pay::class), $config);
        }
    }

    /**
     * initContainer.
     *
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    private function initContainer(): void
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(false);

        try {
            $container = $builder->build();
            $container->set(ContainerInterface::class, $container);
            $container->set(\Psr\Container\ContainerInterface::class, $container);
            $container->set(Pay::class, $this);

            self::$container = $container;
        } catch (Throwable $e) {
            throw new ContainerException($e->getMessage());
        }
    }

    /**
     * register services.
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    private function registerServices(array $config): void
    {
        foreach (array_merge($this->coreService, $this->service) as $service) {
            self::registerService($service, $config);
        }
    }
}
