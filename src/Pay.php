<?php

namespace Yansongda\Pay;

use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Exception\ContainerDependencyException;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ContainerNotFoundException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Service\ConfigServiceProvider;
use Yansongda\Pay\Service\EventServiceProvider;
use Yansongda\Pay\Service\LoggerServiceProvider;

class Pay
{
    /**
     * production mode.
     */
    const MODE_PRODUCTION = 'production';

    /**
     * sandbox mode.
     */
    const MODE_SANDBOX = 'sandbox';

    /**
     * service.
     *
     * @var string[]
     */
    protected $service = [
    ];

    /**
     * baseService.
     *
     * @var string[]
     */
    private $baseService = [
        ConfigServiceProvider::class,
        LoggerServiceProvider::class,
        EventServiceProvider::class,
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
        $pay = new self($config);

        return $pay->get($service);
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
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    public static function set(string $key, $value): void
    {
        self::getContainer()->set($key, $value);
    }

    /**
     * getContainer.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    public static function getContainer(?array $forceInitConfig = null): Container
    {
        if (self::$container instanceof Container) {
            return self::$container;
        }

        if (is_null($forceInitConfig)) {
            throw new ContainerNotFoundException('You Must Init The Container First');
        }

        new self($forceInitConfig);

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
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    private function initContainer()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(false);

        try {
            self::$container = $builder->build();
        } catch (Exception $e) {
            throw new ContainerException($e->getMessage());
        }

        self::set('container', self::$container);
        self::set('pay', $this);
    }

    /**
     * registerService.
     *
     * @author yansongda <me@yansongda.cn>
     */
    private function registerService(array $config): void
    {
        foreach (array_merge($this->baseService, $this->service) as $service) {
            $var = new $service();

            if ($var instanceof ServiceProviderInterface) {
                $var->prepare($config);
                $var->register($this);
            }
        }
    }
}
