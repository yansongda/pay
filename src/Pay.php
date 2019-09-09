<?php

namespace Yansongda\Pay;

use Pimple\Container;
use Pimple\Exception\FrozenServiceException;
use Pimple\Exception\UnknownIdentifierException;
use Yansongda\Pay\Contract\ServiceInterface;
use Yansongda\Pay\Exception\GatewayServiceException;
use Yansongda\Pay\Exception\UnknownServiceException;
use Yansongda\Pay\Service\ConfigService;
use Yansongda\Pay\Service\EventService;
use Yansongda\Pay\Service\LoggerService;
use Yansongda\Supports\Str;

/**
 * @author yansongda <me@yansongda.cn>
 *
 * @property \Yansongda\Supports\Logger logger
 * @property \Yansongda\Supports\Logger log
 * @property \Yansongda\Supports\Config config
 * @property \Symfony\Component\EventDispatcher\EventDispatcher event
 */
class Pay extends Container
{
    /**
     * config.
     *
     * @var array
     */
    protected $config;

    /**
     * service.
     *
     * @var array
     */
    protected $service;

    /**
     * baseConfig.
     *
     * @var array
     */
    private $baseConfig;

    /**
     * baseService.
     *
     * @var array
     */
    private $baseService = [
        ConfigService::class,
        LoggerService::class,
        EventService::class,
    ];

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $config
     * @param array $value
     */
    public function __construct(array $config, array $value = [])
    {
        $this->config = $config;

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
     * @throws \Yansongda\Pay\Exception\UnknownServiceException
     *
     * @return mixed
     */
    public function __get(string $key)
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
     * @return \Yansongda\Pay\Contract\ServiceInterface
     */
    public static function __callStatic($method, $params): ServiceInterface
    {
        $app = new static(...$params);

        return $app->create($method);
    }

    /**
     * get.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $key
     *
     * @throws \Yansongda\Pay\Exception\UnknownServiceException
     *
     * @return mixed
     */
    public function get(string $key)
    {
        try {
            $result = $this->offsetGet($key);
        } catch (UnknownIdentifierException $e) {
            throw new UnknownServiceException();
        }

        return $result;
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
        return array_merge($this->baseConfig, $this->config);
    }

    /**
     * create.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $method
     *
     * @throws \Yansongda\Pay\Exception\GatewayServiceException
     *
     * @return ServiceInterface
     */
    protected function create(string $method): ServiceInterface
    {
        if (!isset($this[$method])) {
            $service = __NAMESPACE__.'\\Service\\Gateway\\'.Str::studly($method).'Service';

            if (class_exists($service)) {
                self::make($service);
            }

            throw new GatewayServiceException("Gateway [{$method}] Not Exists");
        }

        return $this[$method];
    }

    /**
     * make.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $service
     *
     * @throws \Yansongda\Pay\Exception\GatewayServiceException
     */
    private function make(string $service): void
    {
        $gatewayService = new $service($this);

        if ($gatewayService instanceof ServiceInterface) {
            $this->registerService($gatewayService);
        }

        throw new GatewayServiceException("Gateway [{$service}] Must Be An Instance Of ServiceInterface");
    }

    /**
     * registerService.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param \Yansongda\Pay\Contract\ServiceInterface|null $service
     */
    private function registerService(?ServiceInterface $service = null): void
    {
        if (!is_null($service)) {
            parent::register($service);

            return;
        }

        foreach (array_merge($this->baseService, $this->service) as $service) {
            parent::register(new $service());
        }
    }
}
