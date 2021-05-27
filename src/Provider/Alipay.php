<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

class Alipay
{
    public const URL = [
        Pay::MODE_NORMAL => 'https://openapi.alipay.com/gateway.do',
        Pay::MODE_SANDBOX => 'https://openapi.alipaydev.com/gateway.do',
        Pay::MODE_SERVICE => 'https://openapi.alipay.com/gateway.do',
    ];

    public function pay(array $order)
    {
    }

    public function find($order): Collection
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

    public function verify($content): Collection
    {
    }

    public function success(): Response
    {
    }

    public function launch(array $params, array $plugins): Collection
    {
    }
}
