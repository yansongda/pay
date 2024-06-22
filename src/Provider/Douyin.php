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
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\AddRadarPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Contract\ProviderInterface;
use Yansongda\Pay\Event\CallbackReceived;
use Yansongda\Pay\Event\MethodCalled;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\ResponsePlugin;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

/**
 * @method Collection|Rocket mini(array $order) 小程序支付
 */
class Douyin implements ProviderInterface
{
    public const URL = [
        Pay::MODE_NORMAL => 'https://developer.toutiao.com/',
        Pay::MODE_SANDBOX => 'https://open-sandbox.douyin.com/',
        Pay::MODE_SERVICE => 'https://developer.toutiao.com/',
    ];

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function __call(string $shortcut, array $params): null|Collection|MessageInterface|Rocket
    {
        $plugin = '\Yansongda\Pay\Shortcut\Douyin\\'.Str::studly($shortcut).'Shortcut';

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

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function query(array $order): Collection|Rocket
    {
        Event::dispatch(new MethodCalled('douyin', __METHOD__, $order, null));

        return $this->__call('query', [$order]);
    }

    /**
     * @throws InvalidParamsException
     */
    public function cancel(array $order): Collection|Rocket
    {
        throw new InvalidParamsException(Exception::PARAMS_METHOD_NOT_SUPPORTED, '参数异常: 微信不支持 cancel API');
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function close(array $order): Collection|Rocket
    {
        Event::dispatch(new MethodCalled('douyin', __METHOD__, $order, null));

        $this->__call('close', [$order]);

        return new Collection();
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function refund(array $order): Collection|Rocket
    {
        Event::dispatch(new MethodCalled('douyin', __METHOD__, $order, null));

        return $this->__call('refund', [$order]);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     */
    public function callback(null|array|ServerRequestInterface $contents = null, ?array $params = null): Collection|Rocket
    {
        $request = $this->getCallbackParams($contents);

        Event::dispatch(new CallbackReceived('douyin', clone $request, $params, null));

        return $this->pay(
            [CallbackPlugin::class],
            ['_request' => $request, '_params' => $params]
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
            [StartPlugin::class],
            $plugins,
            [AddPayloadSignaturePlugin::class, AddPayloadBodyPlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class],
        );
    }

    protected function getCallbackParams(null|array|ServerRequestInterface $contents = null): ServerRequestInterface
    {
        if (is_array($contents) && isset($contents['body'], $contents['headers'])) {
            return new ServerRequest('POST', 'http://localhost', $contents['headers'], $contents['body']);
        }

        if (is_array($contents)) {
            return new ServerRequest('POST', 'http://localhost', [], json_encode($contents));
        }

        if ($contents instanceof ServerRequestInterface) {
            return $contents;
        }

        return ServerRequest::fromGlobals();
    }
}
