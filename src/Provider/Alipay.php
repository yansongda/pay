<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Pay\Event;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\AddSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\CallbackPlugin;
use Yansongda\Pay\Plugin\Alipay\FormatBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\VerifySignaturePlugin;
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
        Pay::MODE_SANDBOX => 'https://openapi-sandbox.dl.alipaydev.com/gateway.do?charset=utf-8',
        Pay::MODE_SERVICE => 'https://openapi.alipay.com/gateway.do?charset=utf-8',
    ];

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function __call(string $shortcut, array $params): null|Collection|MessageInterface
    {
        $plugin = '\\Yansongda\\Pay\\Shortcut\\Alipay\\'.Str::studly($shortcut).'Shortcut';

        return $this->call($plugin, ...$params);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function query(array $order): array|Collection
    {
        Event::dispatch(new Event\MethodCalled('alipay', __METHOD__, $order, null));

        return $this->__call('query', [$order]);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function cancel(array|string $order): null|array|Collection
    {
        $order = is_array($order) ? $order : ['out_trade_no' => $order];

        Event::dispatch(new Event\MethodCalled('alipay', __METHOD__, $order, null));

        return $this->__call('cancel', [$order]);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function close(array|string $order): null|array|Collection
    {
        $order = is_array($order) ? $order : ['out_trade_no' => $order];

        Event::dispatch(new Event\MethodCalled('alipay', __METHOD__, $order, null));

        return $this->__call('close', [$order]);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function refund(array $order): array|Collection
    {
        Event::dispatch(new Event\MethodCalled('alipay', __METHOD__, $order, null));

        return $this->__call('refund', [$order]);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     */
    public function callback(null|array|ServerRequestInterface $contents = null, ?array $params = null): Collection
    {
        $request = $this->getCallbackParams($contents);

        Event::dispatch(new Event\CallbackReceived('alipay', $request->all(), $params, null));

        return $this->pay(
            [CallbackPlugin::class],
            $request->merge($params)->all()
        );
    }

    public function success(): ResponseInterface
    {
        return new Response(200, [], 'success');
    }

    public function mergeCommonPlugins(array $plugins): array
    {
        return array_merge(
            [StartPlugin::class],
            $plugins,
            [FormatBizContentPlugin::class, AddSignaturePlugin::class, AddRadarPlugin::class, VerifySignaturePlugin::class, ResponsePlugin::class, ParserPlugin::class],
        );
    }

    protected function getCallbackParams(array|ServerRequestInterface $contents = null): Collection
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
