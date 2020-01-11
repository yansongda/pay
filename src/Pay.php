<?php

namespace Yansongda\Pay;

use Pimple\Container;
use Pimple\Exception\FrozenServiceException;
use Pimple\Exception\UnknownIdentifierException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Yansongda\Pay\Contract\ServiceInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Exception\ServiceException;
use Yansongda\Pay\Exception\ServiceProviderException;
use Yansongda\Pay\Exception\UnknownServiceException;
use Yansongda\Pay\Service\AlipayServiceProvider;
use Yansongda\Pay\Service\ConfigServiceProvider;
use Yansongda\Pay\Service\EventServiceProvider;
use Yansongda\Pay\Service\LoggerServiceProvider;
use Yansongda\Pay\Service\WechatServiceProvider;
use Yansongda\Supports\Config;
use Yansongda\Supports\Logger;
use Yansongda\Supports\Str;

/**
 * @author yansongda <me@yansongda.cn>
 *
 * @property \Yansongda\Supports\Logger logger
 * @property \Yansongda\Supports\Logger log
 * @property \Yansongda\Supports\Config config
 * @property \Symfony\Component\EventDispatcher\EventDispatcher event
 *
 * @method static Config config($config)
 * @method static Logger logger($config)
 * @method static Logger log($config)
 * @method static EventDispatcher event($config)
 */
class Pay extends Container
{
    /**
     * config.
     *
     * @var array
     */
    protected $userConfig = [];

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
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $c customer config
     */
    public function __construct(array $c, array $value = [])
    {
        $this->userConfig = $c;

        parent::__construct($value);

        $this->registerService();
    }

    /**
     * __set.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param mixed $value
     *
     * @throws \Yansongda\Pay\Exception\FrozenServiceException
     */
    public function __set(string $key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * __get.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Yansongda\Pay\Exception\ServiceException
     * @throws \Yansongda\Pay\Exception\UnknownServiceException
     */
    public function __get(string $key): ServiceInterface
    {
        return $this->get($key);
    }

    /**
     * __callStatic.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param $method
     * @param $params
     *
     * @throws \Yansongda\Pay\Exception\ServiceException
     * @throws \Yansongda\Pay\Exception\ServiceProviderException
     * @throws \Yansongda\Pay\Exception\UnknownServiceException
     */
    public static function __callStatic($method, $params): ServiceInterface
    {
        $app = new static(...$params);

        $app->create($method);

        return $app->get($method);
    }

    /**
     * get.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Yansongda\Pay\Exception\ServiceException
     * @throws \Yansongda\Pay\Exception\UnknownServiceException
     */
    public function get(string $key): ServiceInterface
    {
        try {
            $result = $this->offsetGet($key);
        } catch (UnknownIdentifierException $e) {
            throw new UnknownServiceException();
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
     * @throws \Yansongda\Pay\Exception\FrozenServiceException
     */
    public function set(string $key, $value): void
    {
        try {
            $this->offsetSet($key, $value);
        } catch (FrozenServiceException $e) {
            throw new Exception\FrozenServiceException();
        }
    }

    /**
     * getConfig.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function getUserConfig(): array
    {
        return $this->userConfig;
    }

    /**
     * create.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Yansongda\Pay\Exception\ServiceProviderException
     */
    protected function create(string $method): void
    {
        if (isset($this[$method])) {
            return;
        }

        $service = __NAMESPACE__.'\\Service\\'.Str::studly($method).'Service';

        if (class_exists($service)) {
            self::make($service);
        }

        throw new ServiceProviderException("ServiceProvider [{$method}] Not Exists");
    }

    /**
     * registerService.
     *
     * @author yansongda <me@yansongda.cn>
     */
    private function registerService(?ServiceProviderInterface $service = null): void
    {
        if (!is_null($service)) {
            parent::register($service);

            return;
        }

        foreach (array_merge($this->baseService, $this->service) as $service) {
            parent::register(new $service());
        }
    }

    /**
     * make.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Yansongda\Pay\Exception\ServiceProviderException
     */
    private function make(string $service): void
    {
        $gatewayService = new $service($this);

        if ($gatewayService instanceof ServiceProviderInterface) {
            $this->registerService($gatewayService);
        }

        throw new ServiceProviderException("[{$service}] Must Be An Instance Of ServiceProviderInterface");
    }
}
