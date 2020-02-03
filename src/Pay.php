<?php

namespace Yansongda\Pay;

use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Yansongda\Pay\Contract\ServiceInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Exception\ContainerDependencyException;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ContainerNotFoundException;
use Yansongda\Pay\Exception\ServiceException;
use Yansongda\Pay\Service\AlipayServiceProvider;
use Yansongda\Pay\Service\ConfigServiceProvider;
use Yansongda\Pay\Service\EventServiceProvider;
use Yansongda\Pay\Service\LoggerServiceProvider;
use Yansongda\Pay\Service\WechatServiceProvider;

/**
 * @author yansongda <me@yansongda.cn>
 */
class Pay
{
    /**
     * @var array
     */
    protected $middleware = [];

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
    public function __construct(array $config)
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
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     * @throws \Yansongda\Pay\Exception\ServiceException
     *
     * @return \Yansongda\Pay\Contract\ServiceInterface
     */
    public static function __callStatic(string $service, array $config): ServiceInterface
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
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     * @throws \Yansongda\Pay\Exception\ServiceException
     */
    public function get(string $key): ServiceInterface
    {
        try {
            $result = self::getContainer()->get($key);
        } catch (NotFoundException $e) {
            throw new ContainerNotFoundException($e->getMessage());
        } catch (DependencyException $e) {
            throw new ContainerDependencyException($e->getMessage());
        }

        if ($result instanceof ServiceInterface) {
            return $result;
        }

        throw new ServiceException("Service [{$key}]'s Impl Must Be An Instance Of ServiceInterface");
    }

    /**
     * set.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param mixed $value
     *
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    public function set(string $key, $value): void
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
    public static function getContainer(): Container
    {
        if (self::$container instanceof Container) {
            return self::$container;
        }

        $builder = new ContainerBuilder();

        $builder->useAnnotations(false);

        try {
            self::$container = $builder->build();

            return self::$container;
        } catch (Exception $e) {
            throw new ContainerException($e->getMessage());
        }
    }

    /**
     * setContainer.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Yansongda\Pay\Exception\ContainerException
     *
     * @return void
     */
    private function initContainer()
    {
        self::getContainer();
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
                $var->register();
            }
        }
    }
}
