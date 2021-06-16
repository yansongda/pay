<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Event;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\LaunchPlugin;
use Yansongda\Pay\Plugin\Wechat\PreparePlugin;
use Yansongda\Pay\Plugin\Wechat\SignPlugin;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

class Wechat extends AbstractProvider
{
    public const URL = [
        Pay::MODE_NORMAL => 'https://api.mch.weixin.qq.com/',
        Pay::MODE_SANDBOX => 'https://api.mch.weixin.qq.com/sandboxnew/',
        Pay::MODE_SERVICE => 'https://api.mch.weixin.qq.com/',
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
        $plugin = '\\Yansongda\\Pay\\Plugin\\Wechat\\Shortcut\\'.
            Str::studly($shortcut).'Shortcut';

        if (!class_exists($plugin) || !in_array(ShortcutInterface::class, class_implements($plugin))) {
            throw new InvalidParamsException(InvalidParamsException::SHORTCUT_NOT_FOUND, "[$plugin] is not incompatible");
        }

        Event::dispatch(new Event\MethodCalled($shortcut, $params, null));

        /* @var ShortcutInterface $money */
        $money = Pay::get($plugin);

        return $this->pay(
            $this->mergeCommonPlugins($money->getPlugins(...$params)),
            ...$params
        );
    }

    public function find($order): Collection
    {
    }

    public function cancel($order): Collection
    {
        // TODO: Implement cancel() method.
    }

    public function close($order): Collection
    {
        // TODO: Implement close() method.
    }

    public function refund(array $order): Collection
    {
        // TODO: Implement refund() method.
    }

    public function verify($contents = null, ?array $params = null): Collection
    {
        // TODO: Implement verify() method.
    }

    public function success(): ResponseInterface
    {
        // TODO: Implement success() method.
    }

    public function mergeCommonPlugins(array $plugins): array
    {
        return array_merge(
            [PreparePlugin::class],
            $plugins,
            [SignPlugin::class],
            [LaunchPlugin::class, ParserPlugin::class],
        );
    }
}
