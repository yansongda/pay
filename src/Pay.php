<?php

namespace Yansongda\Pay;

use Pimple\Container;
use Yansongda\Pay\Service\ConfigService;
use Yansongda\Pay\Service\EventService;
use Yansongda\Pay\Service\LoggerService;

class Pay extends Container
{
    /**
     * service.
     *
     * @var array
     */
    protected $baseService = [
        ConfigService::class,
        LoggerService::class,
        EventService::class,
    ];

    public static function __callStatic($method, $params)
    {

        $app = new static();

        $app->create();
    }

    protected function create()
    {
        
    }
}