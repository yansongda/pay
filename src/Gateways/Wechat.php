<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Contracts\GatewayApplicationInterface;

class Application implements GatewayApplicationInterface
{
    public function pay($gateway, $params)
    {
        echo $gateway . ":pay";
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
