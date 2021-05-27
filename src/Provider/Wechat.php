<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Contract\ProviderInterface;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

class Wechat implements ProviderInterface
{
    const URL = [
        Pay::MODE_NORMAL => 'https://api.mch.weixin.qq.com/',
        Pay::MODE_SANDBOX => 'https://api.mch.weixin.qq.com/sandboxnew/',
        Pay::MODE_SERVICE => 'https://api.mch.weixin.qq.com/',
    ];

    public function addMiddleware(PluginInterface $middleware): ProviderInterface
    {
        // TODO: Implement addMiddleware() method.
    }

    public function pay($order, ?array $middleware = [])
    {
        // TODO: Implement pay() method.
    }

    public function find($order, string $type): Collection
    {
        // TODO: Implement find() method.
    }

    public function refund(array $order): Collection
    {
        // TODO: Implement refund() method.
    }

    public function cancel($order): Collection
    {
        // TODO: Implement cancel() method.
    }

    public function close($order): Collection
    {
        // TODO: Implement close() method.
    }

    public function verify($content, bool $refund): Collection
    {
        // TODO: Implement verify() method.
    }

    public function success(): Response
    {
        // TODO: Implement success() method.
    }
}
