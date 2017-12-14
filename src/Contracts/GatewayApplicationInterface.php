<?php

namespace Yansongda\Pay\Contracts;

interface GatewayApplicationInterface
{
    /**
     * To pay.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param string $gateway
     * @param array $params
     */
    public function pay($gateway, $params);

    public function find();

    public function refund();

    public function cancel();

    public function close();

    public function verify();

    public function success();
}
