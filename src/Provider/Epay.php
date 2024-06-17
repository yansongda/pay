<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Event;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Contract\ProviderInterface;
use Yansongda\Pay\Plugin\Epay\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Epay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Epay\CallbackPlugin;
use Yansongda\Pay\Plugin\Epay\ResponsePlugin;
use Yansongda\Pay\Plugin\Epay\StartPlugin;
use Yansongda\Pay\Plugin\Epay\VerifySignaturePlugin;
use Yansongda\Pay\Event\CallbackReceived;
use Yansongda\Pay\Event\MethodCalled;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

/**
 * @method Collection|Rocket scan(array $order) 扫码支付[微信支付宝都可扫描]
 */
class Epay implements ProviderInterface
{
    public const URL = [
        Pay::MODE_NORMAL => 'https://mybank.jsbchina.cn:577/eis/merchant/merchantServices.htm',
        Pay::MODE_SANDBOX => 'https://epaytest.jsbchina.cn:9999/eis/merchant/merchantServices.htm',
    ];

    public function __call($name, $params)
    {
        $plugin = '\\Yansongda\\Pay\\Shortcut\\Epay\\'.Str::studly($name).'Shortcut';

        return Artful::shortcut($plugin, ...$params);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     */
    public function pay(array $plugins, array $params): null|Collection|MessageInterface|Rocket
    {
        return Artful::artful($plugins, $params);
    }

    public function mergeCommonPlugins(array $plugins): array
    {
        return array_merge(
            [StartPlugin::class],
            $plugins,
            [AddPayloadSignPlugin::class, AddRadarPlugin::class, VerifySignaturePlugin::class, ResponsePlugin::class, ParserPlugin::class],
        );
    }

    public function cancel($order): Collection|Rocket
    {
        $order = is_array($order) ? $order : ['outTradeNo' => $order];

        Event::dispatch(new MethodCalled('epay', __METHOD__, $order, null));

        return $this->__call('cancel', [$order]);
    }

    public function close($order): Collection|Rocket
    {
        throw new InvalidParamsException(Exception::PARAMS_METHOD_NOT_SUPPORTED, 'Epay does not support close api');
    }

    public function refund(array $order): Collection|Rocket
    {
        Event::dispatch(new MethodCalled('epay', __METHOD__, $order, null));

        return $this->__call('refund', [$order]);
    }

    public function callback(null|array|ServerRequestInterface $contents = null, ?array $params = null): Collection|Rocket
    {
        $request = $this->getCallbackParams($contents);

        Event::dispatch(new CallbackReceived('epay', $request, $params, null));

        return $this->pay(
            [CallbackPlugin::class],
            ['request' => $request, 'params' => $params]
        );
    }

    public function success(): ResponseInterface
    {
        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            'success',
        );
    }

    public function query(array $order): Collection|Rocket
    {
        $order = is_array($order) ? $order : ['outTradeNo' => $order];

        Event::dispatch(new MethodCalled('epay', __METHOD__, $order, null));

        return $this->__call('query', [$order]);
    }

    protected function getCallbackParams($contents = null): Collection
    {
        if (is_array($contents)) {
            return Collection::wrap($contents);
        }

        if ($contents instanceof ServerRequestInterface) {
            return Collection::wrap($contents->getParsedBody());
        }

        $request = ServerRequest::fromGlobals();

        return Collection::wrap($request->getParsedBody());
    }
}
