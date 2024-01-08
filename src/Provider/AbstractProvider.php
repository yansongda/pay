<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use Psr\Http\Message\MessageInterface;
use Throwable;
use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Contract\HttpClientFactoryInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Contract\ProviderInterface;
use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Event;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Pipeline;

use function Yansongda\Pay\should_do_http_request;

abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function shortcut(string $shortcut, array $params = []): null|Collection|MessageInterface|Rocket
    {
        if (!class_exists($shortcut) || !in_array(ShortcutInterface::class, class_implements($shortcut))) {
            throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_NOT_FOUND, "参数异常: [{$shortcut}] 未实现 `ShortcutInterface`");
        }

        /* @var ShortcutInterface $shortcutInstance */
        $shortcutInstance = Pay::get($shortcut);

        return $this->pay($shortcutInstance->getPlugins($params), $params);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     */
    public function pay(array $plugins, array $params): null|Collection|MessageInterface|Rocket
    {
        Logger::info('[AbstractProvider] 即将进行 pay 操作', func_get_args());

        Event::dispatch(new Event\PayStart($plugins, $params, null));

        $this->verifyPlugin($plugins);

        /* @var Pipeline $pipeline */
        $pipeline = Pay::make(Pipeline::class);

        /* @var Rocket $rocket */
        $rocket = $pipeline
            ->send((new Rocket())->setParams($params)->setPayload(new Collection()))
            ->through($plugins)
            ->via('assembly')
            ->then(fn ($rocket) => $this->ignite($rocket));

        Event::dispatch(new Event\PayEnd($rocket));

        if (!empty($params['_return_rocket'])) {
            return $rocket;
        }

        return $rocket->getDestination();
    }

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     * @throws InvalidResponseException
     * @throws InvalidConfigException
     */
    public function ignite(Rocket $rocket): Rocket
    {
        if (!should_do_http_request($rocket->getDirection())) {
            return $rocket;
        }

        /* @var HttpClientFactoryInterface $httpFactory */
        $httpFactory = Pay::get(HttpClientFactoryInterface::class);
        /* @var Config $config */
        $config = Pay::get(ConfigInterface::class);

        if (!$httpFactory instanceof HttpClientFactoryInterface) {
            throw new InvalidConfigException(Exception::CONFIG_HTTP_CLIENT_FACTORY_INVALID, '配置异常: 配置的 HttpClientFactoryInterface 不符合规范');
        }

        Logger::info('[AbstractProvider] 准备请求支付服务商 API', $rocket->toArray());

        $http = $httpFactory->create(array_merge($config->get('httpFactory', []), $rocket->getPayload()?->get('_http') ?? []));

        Event::dispatch(new Event\ApiRequesting($rocket));

        try {
            $response = $http->sendRequest($rocket->getRadar());

            $rocket->setDestination(clone $response)
                ->setDestinationOrigin(clone $response);
        } catch (Throwable $e) {
            Logger::error('[AbstractProvider] 请求支付服务商 API 出错', ['message' => $e->getMessage(), 'rocket' => $rocket->toArray(), 'trace' => $e->getTrace()]);

            throw new InvalidResponseException(Exception::REQUEST_RESPONSE_ERROR, '响应异常: 请求支付服务商 API 出错 - '.$e->getMessage(), [], $e);
        }

        Logger::info('[AbstractProvider] 请求支付服务商 API 成功', ['rocket' => $rocket->toArray()]);

        Event::dispatch(new Event\ApiRequested($rocket));

        return $rocket;
    }

    /**
     * @throws InvalidParamsException
     */
    protected function verifyPlugin(array $plugins): void
    {
        foreach ($plugins as $plugin) {
            if (is_callable($plugin)) {
                continue;
            }

            if ((is_object($plugin)
                    || (is_string($plugin) && class_exists($plugin)))
                && in_array(PluginInterface::class, class_implements($plugin))) {
                continue;
            }

            throw new InvalidParamsException(Exception::PARAMS_PLUGIN_INCOMPATIBLE, "参数异常: [{$plugin}] 插件未实现 `PluginInterface`");
        }
    }
}
