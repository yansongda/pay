<?php

namespace Yansongda\Pay\Events;

class RequestReceived extends Event
{
    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $driver
     * @param string $gateway
     */
    public function __construct(string $driver, string $gateway)
    {
        parent::__construct($driver, $gateway);
    }
}
