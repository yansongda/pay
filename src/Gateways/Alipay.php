<?php

namespace Yansongda\Pay\Gateways;

use Yansongda\Pay\Contracts\GatewayApplicationInterface;
use Yansongda\Supports\Config;
use Yansongda\Supports\Traits\HasHttpRequest;

class Alipay implements GatewayApplicationInterface
{
    use HasHttpRequest;

    protected $config;

    protected $payload;

    public function __construct($config)
    {
        $this->config = new Config($config);
    }

    public function pay($gateway, $params)
    {
        
    }

    public function verify()
    {
        # code...
    }

    public function find()
    {
        # code...
    }

    public function refund()
    {
        # code...
    }

    public function cancel()
    {
        # code...
    }

    public function close()
    {
        # code...
    }

    public function success()
    {
        # code...
    }

    public function __call($method, $params)
    {
        return self::pay($method, $params);
    }
}
