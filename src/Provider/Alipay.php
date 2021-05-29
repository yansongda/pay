<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\FilterPlugin;
use Yansongda\Pay\Plugin\Alipay\IgnitePlugin;
use Yansongda\Pay\Plugin\Alipay\SignPlugin;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

class Alipay
{
    public const URL = [
        Pay::MODE_NORMAL => 'https://openapi.alipay.com/gateway.do',
        Pay::MODE_SANDBOX => 'https://openapi.alipaydev.com/gateway.do',
        Pay::MODE_SERVICE => 'https://openapi.alipay.com/gateway.do',
    ];

    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     *
     * @return \Yansongda\Supports\Collection
     */
    public function __call(string $method, array $params)
    {
        $plugin = '\\Yansongda\\Pay\\Plugin\\Alipay\\Shortcut\\'.
            Str::studly($method).'Plugin';

        if (!class_exists($plugin) || !in_array(PluginInterface::class, class_implements($plugin))) {
            throw new InvalidParamsException('Shortcut not found', InvalidParamsException::SHORTCUT_NOT_FOUND);
        }

        return $this->pay([$plugin], ...$params);
    }

    /**
     * @return \Yansongda\Supports\Collection
     */
    public function pay(array $plugins, array $order)
    {
        $plugins = array_merge(
            [IgnitePlugin::class],
            $plugins,
            [FilterPlugin::class, SignPlugin::class]
        );

        // todo
        $payload = [];

        return $this->launch($payload);
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

    public function launch(array $payload): Collection
    {
    }
}
