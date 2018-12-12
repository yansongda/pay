<?php

namespace Yansongda\Pay\Events;

use Symfony\Component\EventDispatcher\Event;

class StartingPay extends Event
{
    /**
     * Driver.
     *
     * @var string
     */
    public $driver;

    /**
     * Gateway.
     *
     * @var string
     */
    public $gateway;

    /**
     * Params.
     *
     * @var array
     */
    public $params;

    /**
     * Bootstrap.
     *
     * @param string $driver
     * @param string $gateway
     * @param array  $params
     */
    public function __construct(string $driver, string $gateway, array $params)
    {
        $this->driver = $driver;
        $this->gateway = $gateway;
        $this->params = $params;
    }
}
