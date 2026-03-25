<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Contract\ProviderInterface;
use Yansongda\Pay\Event;
use Yansongda\Pay\Event\CallbackReceived;
use Yansongda\Pay\Event\MethodCalled;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Bestpay\V1\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\CallbackPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\ResponsePlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\StartPlugin;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

/**
 * @method Collection|Rocket web(array $order)  H5网页支付
 * @method Collection|Rocket scan(array $order) 扫码支付
 */
class Bestpay implements ProviderInterface
{
    public const URL = [
        Pay::MODE_NORMAL => 'https://api.bestpay.com.cn/',
        Pay::MODE_SANDBOX => 'https://sandbox.bestpay.com.cn/',
    ];

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function __call(string $shortcut, array $params): Collection|MessageInterface|Rocket|null
    {
        $plugin = '\Yansongda\Pay\Shortcut\Bestpay\\'.Str::studly($shortcut).'Shortcut';

        return Artful::shortcut($plugin, ...$params);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     */
    public function pay(array $plugins, array $params): Collection|MessageInterface|Rocket|null
    {
        return Artful::artful($plugins, $params);
    }

    public function mergeCommonPlugins(array $plugins): array
    {
        return array_merge(
            [StartPlugin::class],
            $plugins,
            [AddPayloadSignPlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class],
        );
    }

    /**
     * @throws InvalidParamsException
     */
    public function cancel(array $order): Collection|Rocket
    {
        throw new InvalidParamsException(Exception::PARAMS_METHOD_NOT_SUPPORTED, '参数异常: 翼支付不支持 cancel API');
    }

    /**
     * @throws InvalidParamsException
     */
    public function close(array $order): Collection|Rocket
    {
        throw new InvalidParamsException(Exception::PARAMS_METHOD_NOT_SUPPORTED, '参数异常: 翼支付不支持 close API');
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function query(array $order): Collection|Rocket
    {
        Event::dispatch(new MethodCalled('bestpay', __METHOD__, $order, null));

        return $this->__call('query', [$order]);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function refund(array $order): Collection|Rocket
    {
        Event::dispatch(new MethodCalled('bestpay', __METHOD__, $order, null));

        return $this->__call('refund', [$order]);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     */
    public function callback(array|ServerRequestInterface|null $contents = null, ?array $params = null): Collection|Rocket
    {
        $request = $this->getCallbackParams($contents);

        Event::dispatch(new CallbackReceived('bestpay', clone $request, $params, null));

        return $this->pay(
            [CallbackPlugin::class],
            ['_request' => $request, '_params' => $params],
        );
    }

    public function success(): ResponseInterface
    {
        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['returnCode' => '0000', 'returnMsg' => '成功']),
        );
    }

    protected function getCallbackParams(array|ServerRequestInterface|null $contents = null): Collection
    {
        if (is_array($contents)) {
            return Collection::wrap($contents);
        }

        if (!$contents instanceof ServerRequestInterface) {
            $contents = ServerRequest::fromGlobals();
        }

        $body = Collection::wrapJson((string) $contents->getBody());

        if ($body->isNotEmpty()) {
            return $body;
        }

        return Collection::wrap($contents->getParsedBody());
    }
}
