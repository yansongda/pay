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
use Yansongda\Pay\Service\WechatServiceProvider;

/**
 * @method static mixed get(string $name)
 * @method static mixed make($name, array $parameters = [])
 * @method static mixed has(string $name)
 * @method static object injectOn($instance)
 * @method static mixed call($callable, array $parameters = [])
 * @method static void set(string $name, $value)
 * @method static array getKnownEntryNames()
 * @method static string debugEntry(string $name)
 * @method static string getEntryType($entry)
 */
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
     * @var \DI\Container
     */
    private static $container;

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    private function __construct(array $config)
    {
        $this->initContainer();
        $this->registerServices($config);
    }

    /**
     * __callStatic.
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
    public static function __callStatic(string $service, array $config)
    {
        $container = self::hasContainer() ? self::getContainer() : self::getContainer(...$config);

        if (!is_callable([$container, $service])) {
            return self::get($service);
        }

        try {
            return call_user_func_array([$container, $service], $config);
        } catch (NotFoundException $e) {
            throw new ServiceNotFoundException($e->getMessage());
        } catch (DependencyException $e) {
            throw new ContainerDependencyException($e->getMessage());
        } catch (Exception $e) {
            throw new ContainerException($e->getMessage());
        }
    }

    /**
     * getContainer.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     */
    public static function getContainer(?array $initConfig = null): Container
    {
        if (self::hasContainer()) {
            return self::$container;
        }

        if (is_null($initConfig) || !is_array($initConfig)) {
            throw new ContainerNotFoundException('You must init the container first with config');
        }

        new self($initConfig);

        return self::$container;
    }

    /**
     * has Container.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public static function hasContainer(): bool
    {
        return self::$container instanceof Container;
    }

    /**
     * @author yansongda <me@yansongda.cn>
     */
    public static function registerService(string $service, array $config)
    {
        $var = self::get($service);

        if ($var instanceof ServiceProviderInterface) {
            $var->prepare($config);
            $var->register(self::get(Pay::class));
        }
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
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    private function initContainer(): void
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(false);

        try {
            $container = $builder->build();
            $container->set(ContainerInterface::class, $this);
            $container->set(Pay::class, $this);

            self::$container = $container;
        } catch (Exception $e) {
            throw new ContainerException($e->getMessage());
        }
    }

    /**
     * register.
     *
     * @author yansongda <me@yansongda.cn>
     */
    private function registerServices(array $config): void
    {
        foreach (array_merge($this->coreService, $this->service) as $service) {
            self::registerService($service, $config);
        }
    }
}
