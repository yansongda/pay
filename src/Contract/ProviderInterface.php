<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Psr\Http\Message\ResponseInterface;
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
     * @return \Psr\Http\Message\MessageInterface|\Yansongda\Supports\Collection|array|null
     */
    public function pay(array $plugins, array $params);

    /**
     * Quick road - Query an order.
     *
     * @param string|array $order
     *
     * @return array|\Yansongda\Supports\Collection
     */
    public function find($order);

    /**
     * Quick road - Cancel an order.
     *
     * @param string|array $order
     *
     * @return array|\Yansongda\Supports\Collection|void
     */
    public function cancel($order);

    /**
     * Quick road - Close an order.
     *
     * @param string|array $order
     *
     * @return array|\Yansongda\Supports\Collection|void
     */
    public function close($order);

    /**
     * Quick road - Refund an order.
     *
     * @return array|\Yansongda\Supports\Collection
     */
    public function refund(array $order);

    /**
     * Verify a request.
     *
     * @param array|\Psr\Http\Message\ServerRequestInterface|null $contents
     */
    public function callback($contents = null, ?array $params = null): Collection;

    /**
     * Echo success to server.
     */
    public function success(): ResponseInterface;
}
