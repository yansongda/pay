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
     *
     * @return Yansongda\Supports\Collection|Symfony\Component\HttpFoundation\Response 
     */
    public function pay($gateway, $params);

    public function find();

    public function refund();

    public function cancel();

    public function close();

    public function verify();

    /**
     * Echo success to server.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function success();
}
