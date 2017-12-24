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

    public function find($order)
    {
        # code...
    }

    public function refund(array $order)
    {
        # code...
    }

    public function cancel($order)
    {
        # code...
    }

    public function close($order)
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
