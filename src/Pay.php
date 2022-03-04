<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Closure;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ContainerNotFoundException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Service\AlipayServiceProvider;
use Yansongda\Pay\Service\ConfigServiceProvider;
use Yansongda\Pay\Service\ContainerServiceProvider;
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
     * @var \Closure|\Psr\Container\ContainerInterface|null
     */
    private static $container = null;

    /**
     * @param \Closure|\Psr\Container\ContainerInterface|null $container
     *
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    private function __construct(array $config, $container = null)
    {
        $this->registerContainer($container);
        $this->registerServices($config);
    }

    /**
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
     * @param \Closure|\Psr\Container\ContainerInterface|null $container
     *
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public static function config(array $config = [], $container = null): Pay
    {
        if (self::hasContainer() && !($config['_force'] ?? false)) {
            return self::get(Pay::class);
        }

        return new self($config, $container);
    }

    /**
     * @param mixed $value
     *
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    public static function set(string $name, $value): void
    {
        try {
            $container = Pay::getContainer();

            if ($container instanceof Contract\ContainerInterface || method_exists($container, 'set')) {
                $container->set(...func_get_args());

                return;
            }
        } catch (ContainerNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new ContainerException($e->getMessage());
        }

        throw new ContainerException('Current container does NOT support `set` method');
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     *
     * @return mixed
     */
    public static function make(string $service, array $parameters = [])
    {
        try {
            $container = Pay::getContainer();

            if ($container instanceof Contract\ContainerInterface || method_exists($container, 'make')) {
                return $container->make(...func_get_args());
            }
        } catch (ContainerNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new ContainerException($e->getMessage());
        }

        $parameters = array_values($parameters);

        return new $service(...$parameters);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\ContainerException
     *
     * @return mixed
     */
    public static function get(string $service)
    {
        try {
            return Pay::getContainer()->get($service);
        } catch (NotFoundExceptionInterface $e) {
            throw new ServiceNotFoundException($e->getMessage());
        } catch (Throwable $e) {
            throw new ContainerException($e->getMessage());
        }
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     */
    public static function has(string $service): bool
    {
        return Pay::getContainer()->has($service);
    }

    /**
     * @param \Closure|\Psr\Container\ContainerInterface|null $container
     */
    public static function setContainer($container): void
    {
        self::$container = $container;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     */
    public static function getContainer(): ContainerInterface
    {
        if (self::$container instanceof ContainerInterface) {
            return self::$container;
        }

        if (self::$container instanceof Closure) {
            return (self::$container)();
        }

        throw new ContainerNotFoundException('`getContainer()` failed! Maybe you should `setContainer()` first', Exception\Exception::CONTAINER_NOT_FOUND);
    }

    public static function hasContainer(): bool
    {
        return self::$container instanceof ContainerInterface || self::$container instanceof Closure;
    }

    public static function clear(): void
    {
        self::$container = null;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public static function registerService(string $service, array $config): void
    {
        $var = self::get($service);

        if ($var instanceof ServiceProviderInterface) {
            $var->register($config);
        }
    }

    /**
     * @param \Closure|\Psr\Container\ContainerInterface|null $container
     *
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    private function registerContainer($container = null): void
    {
        if (self::$container instanceof ContainerInterface || self::$container instanceof Closure) {
            self::$container = $container;

            return;
        }

        $this->tryRegisterContainer();
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    private function registerServices(array $config): void
    {
        foreach (array_merge($this->coreService, $this->service) as $service) {
            self::registerService($service, $config);
        }
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    private function tryRegisterContainer(): void
    {
        $cs = new ContainerServiceProvider();

        $cs->register();
    }
}
