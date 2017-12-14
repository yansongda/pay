<?php

namespace Yansongda\Pay\Contracts;

interface GatewayApplicationInterface
{
    public function pay($gateway, $params);

    public function find();

    public function refund();

    public function cancel();

    public function close();

    public function verify();

    public function success();
}
