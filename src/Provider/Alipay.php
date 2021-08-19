<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Pay\Event;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\CallbackPlugin;
use Yansongda\Pay\Plugin\Alipay\LaunchPlugin;
use Yansongda\Pay\Plugin\Alipay\PreparePlugin;
use Yansongda\Pay\Plugin\Alipay\RadarPlugin;
use Yansongda\Pay\Plugin\Alipay\SignPlugin;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

/**
 * @method ResponseInterface app(array $order)      APP 支付
 * @method Collection        pos(array $order)      刷卡支付
 * @method Collection        scan(array $order)     扫码支付
 * @method Collection        transfer(array $order) 帐户转账
 * @method ResponseInterface wap(array $order)      手机网站支付
 * @method ResponseInterface web(array $order)      电脑支付
 * @method Collection        mini(array $order)     小程序支付
 */
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
     * @return \Psr\Http\Message\MessageInterface|\Yansongda\Supports\Collection|array|null
     */
    public function __call(string $shortcut, array $params)
    {
        $plugin = '\\Yansongda\\Pay\\Plugin\\Alipay\\Shortcut\\'.
            Str::studly($shortcut).'Shortcut';

        return $this->call($plugin, ...$params);
    }

    /**
     * @param string|array $order
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     *
     * @return array|\Yansongda\Supports\Collection
     */
    public function find($order)
    {
        $order = is_array($order) ? $order : ['out_trade_no' => $order];

        Event::dispatch(new Event\MethodCalled('wechat', __METHOD__, $order, null));

        return $this->__call('query', [$order]);
    }

    /**
     * @param string|array $order
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     *
     * @return array|\Yansongda\Supports\Collection
     */
    public function cancel($order)
    {
        $order = is_array($order) ? $order : ['out_trade_no' => $order];

        Event::dispatch(new Event\MethodCalled('wechat', __METHOD__, $order, null));

        return $this->__call('cancel', [$order]);
    }

    /**
     * @param string|array $order
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     *
     * @return array|\Yansongda\Supports\Collection
     */
    public function close($order)
    {
        $order = is_array($order) ? $order : ['out_trade_no' => $order];

        Event::dispatch(new Event\MethodCalled('wechat', __METHOD__, $order, null));

        return $this->__call('close', [$order]);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     *
     * @return array|\Yansongda\Supports\Collection
     */
    public function refund(array $order)
    {
        Event::dispatch(new Event\MethodCalled('wechat', __METHOD__, $order, null));

        return $this->__call('refund', [$order]);
    }

    /**
     * @param array|\Psr\Http\Message\ServerRequestInterface|null $contents
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function callback($contents = null, ?array $params = null): Collection
    {
        Event::dispatch(new Event\CallbackReceived('alipay', $contents, $params, null));

        $request = $this->getCallbackParams($contents);

        return $this->pay(
            [CallbackPlugin::class], $request->merge($params)->all()
        );
    }

    public function success(): ResponseInterface
    {
        return new Response(200, [], 'success');
    }

    public function mergeCommonPlugins(array $plugins): array
    {
        return array_merge(
            [PreparePlugin::class],
            $plugins,
            [SignPlugin::class, RadarPlugin::class],
            [LaunchPlugin::class, ParserPlugin::class],
        );
    }

    /**
     * @param array|ServerRequestInterface|null $contents
     */
    protected function getCallbackParams($contents = null): Collection
    {
        if (is_array($contents)) {
            return Collection::wrap($contents);
        }

        if ($contents instanceof ServerRequestInterface) {
            return Collection::wrap('GET' === $contents->getMethod() ? $contents->getQueryParams() :
                $contents->getParsedBody());
        }

        $request = ServerRequest::fromGlobals();

        return Collection::wrap(
            array_merge($request->getQueryParams(), $request->getParsedBody())
        );
    }
}
