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
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Contract\ProviderInterface;
use Yansongda\Pay\Event;
use Yansongda\Pay\Event\CallbackReceived;
use Yansongda\Pay\Event\MethodCalled;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\CallbackPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AppCallbackPlugin;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

/**
 * @method ResponseInterface|Rocket app(array<string, mixed> $order)      APP 支付
 * @method Collection|Rocket        pos(array<string, mixed> $order)      刷卡支付（付款码，被扫码）
 * @method Collection|Rocket        scan(array<string, mixed> $order)     扫码支付（摄像头，主动扫）
 * @method Collection|Rocket        transfer(array<string, mixed> $order) 帐户转账
 * @method ResponseInterface|Rocket h5(array<string, mixed> $order)       手机网站支付
 * @method ResponseInterface|Rocket web(array<string, mixed> $order)      电脑支付
 * @method Collection|Rocket        mini(array<string, mixed> $order)     小程序支付
 */
class Alipay implements ProviderInterface
{
    /**
     * 支付宝网关域名（V2/V3 共用：V2 拼接时追加 `gateway.do`，V3 直接拼 `/v3/` 路径）.
     */
    public const URL = [
        Pay::MODE_NORMAL => 'https://openapi.alipay.com',
        Pay::MODE_SANDBOX => 'https://openapi-sandbox.dl.alipaydev.com',
        Pay::MODE_SERVICE => 'https://openapi.alipay.com',
    ];

    /**
     * 支付宝 V3 沙箱网关（官方 V3 SDK 沙箱 host，与 V2 沙箱域名不同）.
     */
    public const V3_SANDBOX_URL = 'http://openapi.sandbox.dl.alipaydev.com';

    /**
     * Alipay V3 已支持的 shortcut（接口级自动分流：命中则直接走 V3 最新版接口，其余走 V2）.
     */
    public const V3_SHORTCUTS = ['pos', 'scan', 'query', 'refund', 'cancel', 'close'];

    /**
     * @param array<int, mixed> $params
     *
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function __call(string $shortcut, array $params): Collection|MessageInterface|Rocket|null
    {
        $shortcut = strtolower($shortcut);

        if (in_array($shortcut, self::V3_SHORTCUTS, true)) {
            return Artful::shortcut('\Yansongda\Pay\Shortcut\Alipay\V3\\'.Str::studly($shortcut).'Shortcut', ...$params);
        }

        return Artful::shortcut('\Yansongda\Pay\Shortcut\Alipay\\'.Str::studly($shortcut).'Shortcut', ...$params);
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
        Event::dispatch(new MethodCalled(Pay::PROVIDER_ALIPAY, __METHOD__, $order, null));

        return $this->__call('query', [$order]);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function cancel(array $order): Collection|Rocket
    {
        Event::dispatch(new MethodCalled(Pay::PROVIDER_ALIPAY, __METHOD__, $order, null));

        return $this->__call('cancel', [$order]);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function close(array $order): Collection|Rocket
    {
        Event::dispatch(new MethodCalled(Pay::PROVIDER_ALIPAY, __METHOD__, $order, null));

        return $this->__call('close', [$order]);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function refund(array $order): Collection|Rocket
    {
        Event::dispatch(new MethodCalled(Pay::PROVIDER_ALIPAY, __METHOD__, $order, null));

        return $this->__call('refund', [$order]);
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function callback(array|ServerRequestInterface|null $contents = null, ?array $params = null): Collection
    {
        $request = $this->getCallbackParams($contents);

        Event::dispatch(new CallbackReceived(Pay::PROVIDER_ALIPAY, $request->all(), $params, null));

        return $this->pay([CallbackPlugin::class], $request->merge($params ?? [])->all());
    }

    /**
     * @param null|array<string, mixed>|ServerRequestInterface $contents
     * @param null|array<string, mixed>                        $params
     *
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function appCallback(array|ServerRequestInterface|null $contents = null, ?array $params = null): Collection
    {
        $request = $this->getCallbackParams($contents);

        return $this->pay([AppCallbackPlugin::class], $request->merge($params ?? [])->all());
    }

    public function success(): ResponseInterface
    {
        return new Response(200, [], 'success');
    }

    /**
     * 提取回调参数：数组直接使用；`body`/`headers` 形态（webhook 转发）解析 form 串；
     * ServerRequest 按 GET/POST 取参数；空则从全局请求读取.
     *
     * @param null|array<string, mixed>|ServerRequestInterface $contents
     */
    protected function getCallbackParams(array|ServerRequestInterface|null $contents = null): Collection
    {
        if ($contents instanceof ServerRequestInterface) {
            return Collection::wrap('GET' === $contents->getMethod() ? $contents->getQueryParams()
                : $contents->getParsedBody());
        }

        if (is_array($contents) && isset($contents['body'], $contents['headers'])) {
            parse_str((string) $contents['body'], $parsedBody);

            return Collection::wrap($parsedBody);
        }

        if (is_array($contents)) {
            return Collection::wrap($contents);
        }

        $request = ServerRequest::fromGlobals();

        return Collection::wrap(
            array_merge($request->getQueryParams(), $request->getParsedBody() ?? [])
        );
    }
}
