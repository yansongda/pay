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
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Contract\ProviderInterface;
use Yansongda\Pay\Event;
use Yansongda\Pay\Event\CallbackReceived;
use Yansongda\Pay\Event\MethodCalled;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\CallbackPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\CallbackPlugin as RefundCallbackPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\PreRefundCallbackPlugin;
use Yansongda\Pay\Traits\DouyinTrait;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

/**
 * @method Collection|Rocket mini(array<string, mixed> $order) 小程序下单签名（新交易系统）
 */
class Douyin implements ProviderInterface
{
    use DouyinTrait;

    public const URL = [
        Pay::MODE_NORMAL => 'https://open.douyin.com',
        Pay::MODE_SANDBOX => 'https://open-sandbox.douyin.com',
        Pay::MODE_SERVICE => 'https://open.douyin.com',
    ];

    /**
     * @param array<int, mixed> $params
     *
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function __call(string $shortcut, array $params): Collection|MessageInterface|Rocket|null
    {
        $plugin = '\Yansongda\Pay\Shortcut\Douyin\\'.Str::studly($shortcut).'Shortcut';

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

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function query(array $order): Collection|Rocket
    {
        Event::dispatch(new MethodCalled(Pay::PROVIDER_DOUYIN, __METHOD__, $order, null));

        return $this->__call('query', [$order]);
    }

    /**
     * @throws InvalidParamsException
     */
    public function cancel(array $order): Collection|Rocket
    {
        throw new InvalidParamsException(Exception::PARAMS_METHOD_NOT_SUPPORTED, '参数异常: 抖音不支持 cancel API');
    }

    /**
     * @throws InvalidParamsException
     */
    public function close(array $order): Collection|Rocket
    {
        throw new InvalidParamsException(Exception::PARAMS_METHOD_NOT_SUPPORTED, '参数异常: 抖音不支持 close API');
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function refund(array $order): Collection|Rocket
    {
        Event::dispatch(new MethodCalled(Pay::PROVIDER_DOUYIN, __METHOD__, $order, null));

        return $this->__call('refund', [$order]);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     */
    public function callback(array|ServerRequestInterface|null $contents = null, ?array $params = null): Collection|Rocket
    {
        if ($contents instanceof ServerRequestInterface) {
            $request = $contents;
        } elseif (null === $contents) {
            $request = ServerRequest::fromGlobals();
        } else {
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, '参数异常: 抖音新交易系统回调需要携带回调头信息以完成验签，仅支持 ServerRequestInterface 入参');
        }

        Event::dispatch(new CallbackReceived(Pay::PROVIDER_DOUYIN, clone $request, $params, null));

        return $this->pay([CallbackPlugin::class], array_merge($params ?? [], ['_request' => $request]));
    }

    /**
     * @param null|array<string, mixed> $params
     *
     * @throws ContainerException
     * @throws InvalidParamsException
     */
    public function refundCallback(ServerRequestInterface $request, ?array $params = null): Collection|Rocket
    {
        Event::dispatch(new CallbackReceived(Pay::PROVIDER_DOUYIN, clone $request, $params, null));

        return $this->pay([RefundCallbackPlugin::class], array_merge($params ?? [], ['_request' => $request]));
    }

    /**
     * @param null|array<string, mixed> $params
     *
     * @throws ContainerException
     * @throws InvalidParamsException
     */
    public function preRefundCallback(ServerRequestInterface $request, ?array $params = null): Collection|Rocket
    {
        Event::dispatch(new CallbackReceived(Pay::PROVIDER_DOUYIN, clone $request, $params, null));

        return $this->pay([PreRefundCallbackPlugin::class], array_merge($params ?? [], ['_request' => $request]));
    }

    public function success(): ResponseInterface
    {
        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['err_no' => 0, 'err_tips' => 'success']),
        );
    }
}
