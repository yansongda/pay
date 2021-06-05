<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\FilterPlugin;
use Yansongda\Pay\Plugin\Alipay\LaunchPlugin;
use Yansongda\Pay\Plugin\Alipay\PreparePlugin;
use Yansongda\Pay\Plugin\Alipay\RadarPlugin;
use Yansongda\Pay\Plugin\Alipay\SignPlugin;
use Yansongda\Pay\Plugin\Alipay\Trade\QueryPlugin;
use Yansongda\Pay\Plugin\PackerPlugin;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

class Alipay extends AbstractProvider
{
    public const URL = [
        Pay::MODE_NORMAL => 'https://openapi.alipay.com/gateway.do?charset=utf-8',
        Pay::MODE_SANDBOX => 'https://openapi.alipaydev.com/gateway.do?charset=utf-8',
        Pay::MODE_SERVICE => 'https://openapi.alipay.com/gateway.do?charset=utf-8',
    ];

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     *
     * @return \Yansongda\Supports\Collection|\Psr\Http\Message\ResponseInterface
     */
    public function __call(string $shortcut, array $params)
    {
        $plugin = '\\Yansongda\\Pay\\Plugin\\Alipay\\Shortcut\\'.
            Str::studly($shortcut).'Shortcut';

        if (!class_exists($plugin) || !in_array(ShortcutInterface::class, class_implements($plugin))) {
            throw new InvalidParamsException(InvalidParamsException::SHORTCUT_NOT_FOUND, "[$plugin] is not incompatible");
        }

        /* @var ShortcutInterface $money */
        $money = Pay::get($plugin);

        return $this->pay(
            $this->mergeCommonPlugins($money->getPlugins()),
            ...$params
        );
    }

    /**
     * @param string|array $order
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function find($order): Collection
    {
        return $this->pay(
            $this->mergeCommonPlugins([QueryPlugin::class]),
            is_array($order) ? $order : ['out_trade_no' => $order]
        );
    }

    public function mergeCommonPlugins(array $plugins): array
    {
        return array_merge(
            [PreparePlugin::class],
            $plugins,
            [FilterPlugin::class, SignPlugin::class, RadarPlugin::class],
            [LaunchPlugin::class, PackerPlugin::class],
        );
    }
}
