<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\FilterPlugin;
use Yansongda\Pay\Plugin\Alipay\IgnitePlugin;
use Yansongda\Pay\Plugin\Alipay\SignPlugin;
use Yansongda\Pay\Plugin\Alipay\TradePayPlugin;
use Yansongda\Supports\Collection;

class Alipay
{
    public const URL = [
        Pay::MODE_NORMAL => 'https://openapi.alipay.com/gateway.do',
        Pay::MODE_SANDBOX => 'https://openapi.alipaydev.com/gateway.do',
        Pay::MODE_SERVICE => 'https://openapi.alipay.com/gateway.do',
    ];

    public function pay(array $order): Collection
    {
        $plugins = [
            IgnitePlugin::class,
            TradePayPlugin::class,
            FilterPlugin::class,
            SignPlugin::class,
        ];

        return $this->launch($order, $plugins);
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
