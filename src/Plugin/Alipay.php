<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin;

use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Contract\MiddlewareInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

class Alipay
{
    public const URL = [
        Pay::MODE_NORMAL => 'https://openapi.alipay.com/gateway.do',
        Pay::MODE_SANDBOX => 'https://openapi.alipaydev.com/gateway.do',
        Pay::MODE_SERVICE => 'https://openapi.alipay.com/gateway.do',
    ];

    public function pay($order)
    {
    }

    public function find($order, string $type): Collection
    {
    }

    public function refund(array $order): Collection
    {
    }

    public function cancel($order): Collection
    {
    }

    public function close($order): Collection
    {
    }

    public function verify($content, bool $refund): Collection
    {
    }

    public function success(): Response
    {
    }

    public function launch(Collection $payload)
    {
    }
}
