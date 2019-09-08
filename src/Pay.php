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
     * @param $key
     *
     * @throws \Yansongda\Pay\Exception\UnknownServiceException
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * __set.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param $key
     * @param $value
     *
     * @throws \Yansongda\Pay\Exception\FrozenServiceException
     *
     * @return void
     */
    public function __set($key, $value)
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
    public static function __callStatic($method, $params)
    {
        $app = new static(...$params);

        return $app->create($method);
    }

    /**
     * get.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param $key
     *
     * @throws \Yansongda\Pay\Exception\UnknownServiceException
     *
     * @return mixed
     */
    public function get($key)
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
     * @param $key
     * @param $value
     *
     * @throws \Yansongda\Pay\Exception\FrozenServiceException
     *
     * @return void
     */
    public function set($key, $value)
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
     * @param $method
     *
     * @throws \Yansongda\Pay\Exception\GatewayServiceException
     *
     * @return ServiceInterface
     */
    protected function create($method)
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
     * @param $service
     *
     * @throws \Yansongda\Pay\Exception\GatewayServiceException
     *
     * @return void
     */
    private function make($service)
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
     *
     * @return void
     */
    private function registerService(?ServiceInterface $service = null)
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
