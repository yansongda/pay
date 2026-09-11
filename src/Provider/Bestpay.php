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
use Yansongda\Pay\Plugin\Bestpay\V1\CallbackPlugin;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

/**
 * @method Collection|Rocket web(array<string, mixed> $order)  PC 收银台
 * @method Collection|Rocket h5(array<string, mixed> $order)   手机收银台
 * @method Collection|Rocket scan(array<string, mixed> $order) 聚合收款码
 */
class Bestpay implements ProviderInterface
{
    public const URL = [
        Pay::MODE_NORMAL => 'https://mapi.bestpay.com.cn/mapi',
        Pay::MODE_SANDBOX => 'https://mapi.bestpay.com.cn/mapi',
    ];

    public const PATH_SDK_REQUEST = '/sdkRequest';

    /**
     * @param array<int, mixed> $params
     *
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

    /**
     * @throws InvalidParamsException
     */
    public function cancel(array $order): Collection|Rocket
    {
        throw new InvalidParamsException(Exception::PARAMS_METHOD_NOT_SUPPORTED, '参数异常: 翼支付不支持 cancel API');
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function close(array $order): Collection|Rocket
    {
        Event::dispatch(new MethodCalled(Pay::PROVIDER_BESTPAY, __METHOD__, $order, null));

        return $this->__call('close', [$order]);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function query(array $order): Collection|Rocket
    {
        Event::dispatch(new MethodCalled(Pay::PROVIDER_BESTPAY, __METHOD__, $order, null));

        return $this->__call('query', [$order]);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function refund(array $order): Collection|Rocket
    {
        Event::dispatch(new MethodCalled(Pay::PROVIDER_BESTPAY, __METHOD__, $order, null));

        return $this->__call('refund', [$order]);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     */
    public function callback(array|ServerRequestInterface|null $contents = null, ?array $params = null): Collection|Rocket
    {
        $request = $this->getCallbackParams($contents);

        Event::dispatch(new CallbackReceived(Pay::PROVIDER_BESTPAY, $request->all(), $params, null));

        return $this->pay(
            [CallbackPlugin::class],
            ['request' => $request, 'params' => $params],
        );
    }

    /**
     * 异步通知成功应答.
     *
     * 注意：应答格式 `{"resultCode":"SUCCESS","resultMsg":"OK"}` 以翼支付开发者文档
     * `aggregatePayOrRefundNotify` 契约为准（失败应答 `{"resultCode":"FAILED","resultMsg":"FAILED"}`），
     * 因官方文档需商户登录，建议沙箱联调时复核。
     */
    public function success(): ResponseInterface
    {
        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['resultCode' => 'SUCCESS', 'resultMsg' => 'OK'], JSON_UNESCAPED_UNICODE),
        );
    }

    /**
     * @param null|array<string, mixed>|ServerRequestInterface $contents
     */
    protected function getCallbackParams(array|ServerRequestInterface|null $contents = null): Collection
    {
        if (is_array($contents)) {
            return Collection::wrap($contents);
        }

        if ($contents instanceof ServerRequestInterface) {
            $body = Collection::wrapJson((string) $contents->getBody());

            if ($body->isNotEmpty()) {
                return $body;
            }

            return Collection::wrap($contents->getParsedBody());
        }

        $request = ServerRequest::fromGlobals();
        $body = Collection::wrapJson((string) $request->getBody());

        if ($body->isNotEmpty()) {
            return $body;
        }

        return Collection::wrap($request->getParsedBody());
    }
}
