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
use Yansongda\Pay\Service\ConfigServiceProvider;
use Yansongda\Pay\Service\EventServiceProvider;
use Yansongda\Pay\Service\AlipayServiceProvider;
use Yansongda\Pay\Service\WechatServiceProvider;
use Yansongda\Pay\Service\LoggerServiceProvider;
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
    protected $c = [];

    /**
     * service.
     *
     * @var string[]
     */
    protected $service = [
        AlipayServiceProvider::class,
        WechatServiceProvider::class
    ];

    /**
     * baseConfig.
     *
     * @var array
     */
    private $baseConfig = [
        'log' => [
            'enable' => true,
            'file' => null,
            'identify' => 'yansongda.supports',
            'level' => 'debug',
            'type' => 'daily',
            'max_files' => 30
        ],
        'http' => [
            'timeout' => 5.0,
            'connect_timeout' => 3.0,
        ],
        'mode' => 'normal',
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
     * @param array $value
     */
    public function __construct(array $c, array $value = [])
    {
        $this->c = $c;

        parent::__construct($value);

        $this->registerService();
    }

    /**
     * __get.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $key
     *
     * @throws \Yansongda\Pay\Exception\ServiceException
     * @throws \Yansongda\Pay\Exception\UnknownServiceException
     *
     * @return ServiceInterface
     */
    public function __get(string $key): ServiceInterface
    {
        return $this->get($key);
    }

    /**
     * __set.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws \Yansongda\Pay\Exception\FrozenServiceException
     */
    public function __set(string $key, $value): void
    {
        $this->set($key, $value);
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
     *
     * @return ServiceInterface
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
     * @param string $key
     *
     * @throws \Yansongda\Pay\Exception\ServiceException
     * @throws \Yansongda\Pay\Exception\UnknownServiceException
     *
     * @return ServiceInterface
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
     * @param string $key
     * @param mixed  $value
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
     *
     * @return array
     */
    public function getConfig(): array
    {
        return array_replace_recursive($this->baseConfig, $this->c);
    }

    /**
     * create.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $method
     *
     * @throws \Yansongda\Pay\Exception\ServiceProviderException
     *
     * @return void
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
     *
     * @param \Yansongda\Pay\Contract\ServiceProviderInterface|null $service
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
     * @param string $service
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
