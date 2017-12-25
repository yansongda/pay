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
     * @param array  $params
     *
     * @return Yansongda\Supports\Collection|Symfony\Component\HttpFoundation\Response
     */
    public function pay($gateway, $params);

    /**
     * Query an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     *
     * @return Yansongda\Supports\Collection
     */
    public function find($order);

    /**
     * Refund an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $order
     *
     * @return Yansongda\Supports\Collection
     */
    public function refund($order);

    /**
     * Cancel an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     *
     * @return Yansongda\Supports\Collection
     */
    public function cancel($order);

    /**
     * Close an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     *
     * @return Yansongda\Supports\Collection
     */
    public function close($order);

    /**
     * Verify a request.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return Yansongda\Supports\Collection
     */
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
