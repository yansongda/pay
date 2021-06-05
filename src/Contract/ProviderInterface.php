<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Symfony\Component\HttpFoundation\Response;
use Yansongda\Supports\Collection;

interface ProviderInterface
{
    /**
     * pay.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return Collection|Response
     */
    public function pay(array $plugins, array $params);

    /**
     * Quick road - Query an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     */
    public function find($order): Collection;

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
