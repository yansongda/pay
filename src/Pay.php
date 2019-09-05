<?php

namespace Yansongda\Pay;

use Pimple\Container;
use Yansongda\Pay\Service\ConfigService;
use Yansongda\Pay\Service\EventService;
use Yansongda\Pay\Service\LoggerService;

class Pay extends Container
{
    /**
     * baseConfig.
     *
     * @var array
     */
    private $baseConfig;

    /**
     * config.
     *
     * @var array
     */
    protected $config;

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
     * service.
     *
     * @var array
     */
    protected $service;

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
        $this->baseConfig = $config;

        parent::__construct($value);
    }

    public static function __callStatic($method, $params)
    {
        $app = new static(...$params);

        $app->create();
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

    protected function create()
    {

    }
}