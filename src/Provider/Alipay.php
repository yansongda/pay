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
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\CallbackPlugin;
use Yansongda\Pay\Plugin\Alipay\GatewayCallbackPlugin;
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
 * @method Collection|Rocket        auth(array<string, mixed> $order)     应用授权令牌（ISV 换取/刷新/查询）
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

        $plugin = '\Yansongda\Pay\Shortcut\Alipay\\'.Str::studly($shortcut).'Shortcut';

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
    public function callback(array|ServerRequestInterface|null $contents = null, ?array $params = null): Collection|ResponseInterface|Rocket
    {
        $request = $this->getCallbackParams($contents);

        Event::dispatch(new CallbackReceived(Pay::PROVIDER_ALIPAY, $request->all(), $params, null));

        // `_action` 从 merge 后数组读取（与微信先例只读第二实参不同），以兼容已拍板入口 callback(['_action' => 'gw'])（_action 位于第一参数 contents）。
        // 注意：webhook 形态（body+headers）下 getCallbackParams 只解析 body，外层 `_action` 会被丢弃，须写在第二实参。
        // 风险已评估：外部注入 _action 必须先过验签（伪造签名必败，只会得到 VERIFY_FAILED XML），异常路径不可达。
        $params = $request->merge($params ?? [])->all();

        $plugins = match ($params['_action'] ?? null) {
            null => [CallbackPlugin::class],
            'gw' => [GatewayCallbackPlugin::class],
            default => throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, '参数异常: 不支持的回调 _action ['.$params['_action'].']'),
        };

        /** @var Collection|ResponseInterface|Rocket $result */
        $result = $this->pay($plugins, $params);

        return $result;
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
