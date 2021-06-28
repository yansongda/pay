<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Pay\Event;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\CallbackPlugin;
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
     * @return \Yansongda\Supports\Collection|\Psr\Http\Message\MessageInterface
     */
    public function __call(string $shortcut, array $params)
    {
        $plugin = '\\Yansongda\\Pay\\Plugin\\Wechat\\Shortcut\\'.
            Str::studly($shortcut).'Shortcut';

        return $this->call($plugin, ...$params);
    }

    /**
     * @param array|string $order
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function find($order): Collection
    {
        $order = is_array($order) ? $order : ['transaction_id' => $order];

        Event::dispatch(new Event\MethodCalled(__METHOD__, $order, null));

        return $this->__call('query', [$order]);
    }

    /**
     * @param array|string $order
     *
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    public function cancel($order): Collection
    {
        throw new InvalidParamsException(InvalidParamsException::METHOD_NOT_SUPPORTED, 'Wechat does not support cancel api');
    }

    /**
     * @param array|string $order
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function close($order): Collection
    {
        $order = is_array($order) ? $order : ['out_trade_no' => $order];

        Event::dispatch(new Event\MethodCalled(__METHOD__, $order, null));

        return $this->__call('close', [$order]);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function refund(array $order): Collection
    {
        Event::dispatch(new Event\MethodCalled(__METHOD__, $order, null));

        return $this->__call('refund', [$order]);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function callback($contents = null, ?array $params = null): Collection
    {
        Event::dispatch(new Event\CallbackReceived('wechat', $contents, $params, null));

        $request = $this->getCallbackParams($contents);

        return $this->pay(
            [CallbackPlugin::class], ['request' => $request, 'params' => $params]
        );
    }

    public function success(): ResponseInterface
    {
        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['code' => 'SUCCESS', 'message' => '成功']),
        );
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

    /**
     * @param array|ServerRequestInterface|null $contents
     */
    protected function getCallbackParams($contents = null): ServerRequestInterface
    {
        if (is_array($contents) && isset($contents['body']) && isset($contents['headers'])) {
            return new ServerRequest('POST', 'localhost', $contents['headers'], $contents['body']);
        }

        if (is_array($contents)) {
            return new ServerRequest('POST', 'localhost', [], $contents);
        }

        if ($contents instanceof ServerRequestInterface) {
            return $contents;
        }

        return ServerRequest::fromGlobals();
    }
}
