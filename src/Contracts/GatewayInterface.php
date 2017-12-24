<?php

namespace Yansongda\Pay\Contracts;

interface GatewayInterface
{
    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     * @param array  $payload
     *
     * @return Yansongda\Supports\Collection|Symfony\Component\HttpFoundation\Response
     */
    public function pay($endpoint, array $payload);
}
