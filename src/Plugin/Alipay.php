<?php

namespace Yansongda\Pay\Plugin;

use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Contract\MiddlewareInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

class Alipay implements PluginInterface
{
    const URL = [
        Pay::MODE_NORMAL => 'https://openapi.alipay.com/gateway.do',
        Pay::MODE_SANDBOX => 'https://openapi.alipaydev.com/gateway.do',
        Pay::MODE_SERVICE => 'https://openapi.alipay.com/gateway.do',
    ];

    /**
     * @inheritDoc
     */
    public function addMiddleware(MiddlewareInterface $middleware): PluginInterface
    {
        // TODO: Implement addMiddleware() method.
    }

    /**
     * @inheritDoc
     */
    public function pay($order, ?array $middleware = [])
    {
        // TODO: Implement pay() method.
    }

    /**
     * {@inheritdoc}
     */
    public function find($order, string $type): Collection
    {
        // TODO: Implement find() method.
    }

    /**
     * {@inheritdoc}
     */
    public function refund(array $order): Collection
    {
        // TODO: Implement refund() method.
    }

    /**
     * {@inheritdoc}
     */
    public function cancel($order): Collection
    {
        // TODO: Implement cancel() method.
    }

    /**
     * {@inheritdoc}
     */
    public function close($order): Collection
    {
        // TODO: Implement close() method.
    }

    /**
     * {@inheritdoc}
     */
    public function verify($content, bool $refund): Collection
    {
        // TODO: Implement verify() method.
    }

    /**
     * {@inheritdoc}
     */
    public function success(): Response
    {
        // TODO: Implement success() method.
    }
}
