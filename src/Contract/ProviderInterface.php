<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Supports\Collection;

interface ProviderInterface
{
    /**
     * pay.
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     *
     * @return \Yansongda\Supports\Collection|\Psr\Http\Message\MessageInterface
     */
    public function pay(array $plugins, array $params);

    /**
     * Quick road - Query an order.
     *
     * @param string|array $order
     */
    public function find($order): Collection;

    /**
     * Quick road - Cancel an order.
     *
     * @param string|array $order
     */
    public function cancel($order): Collection;

    /**
     * Quick road - Close an order.
     *
     * @param string|array $order
     */
    public function close($order): Collection;

    /**
     * Quick road - Refund an order.
     */
    public function refund(array $order): Collection;

    /**
     * Verify a request.
     *
     * @param array|ServerRequestInterface|null $contents
     */
    public function callback($contents = null, ?array $params = null): Collection;

    /**
     * Echo success to server.
     */
    public function success(): ResponseInterface;
}
