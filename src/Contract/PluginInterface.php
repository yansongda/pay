<?php

namespace Yansongda\Pay\Contract;

use Symfony\Component\HttpFoundation\Response;
use Yansongda\Supports\Collection;

interface PluginInterface
{
    /**
     * addMiddleware.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function addMiddleware(MiddlewareInterface $middleware): self;

    /**
     * pay.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array|string          $order      order params
     * @param MiddlewareInterface[] $middleware middleware before pay
     *
     * @return Collection|Response
     */
    public function pay($order, ?array $middleware = []);

    /**
     * Quick road - Query an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     * @param string       $type  query type
     */
    public function find($order, string $type): Collection;

    /**
     * Quick road - Refund an order.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function refund(array $order): Collection;

    /**
     * Quick road - Cancel an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     */
    public function cancel($order): Collection;

    /**
     * Quick road - Close an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     */
    public function close($order): Collection;

    /**
     * Verify a request.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array|null $content content from server
     * @param bool              $refund  is refund?
     */
    public function verify($content, bool $refund): Collection;

    /**
     * Echo success to server.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function success(): Response;
}
