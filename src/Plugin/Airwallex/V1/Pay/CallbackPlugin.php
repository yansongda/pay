<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Airwallex\V1\Pay;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Traits\AirwallexTrait;
use Yansongda\Supports\Collection;

/**
 * @see https://www.airwallex.com/docs/developer-tools/webhooks/listen-for-webhook-events
 */
class CallbackPlugin implements PluginInterface
{
    use AirwallexTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Airwallex][V1][Pay][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->init($rocket);

        /* @phpstan-ignore-next-line */
        self::verifyAirwallexWebhookSign($rocket->getDestinationOrigin(), $rocket->getParams());

        $body = json_decode((string) $rocket->getDestination()->getBody(), true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidParamsException(Exception::PARAMS_AIRWALLEX_BODY_INVALID, '参数异常: Airwallex 回调 Body 解析失败');
        }

        $collection = Collection::wrap($body);
        $rocket->setDirection(NoHttpRequestDirection::class)
            ->setPayload($collection)
            ->setDestination($collection);

        Logger::info('[Airwallex][V1][Pay][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws InvalidParamsException
     */
    protected function init(Rocket $rocket): void
    {
        $request = $rocket->getParams()['_request'] ?? null;
        $params = $rocket->getParams()['_params'] ?? [];

        if (!$request instanceof ServerRequestInterface) {
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, '参数异常: Airwallex 回调请求不正确');
        }

        $rocket->setDestination(clone $request)
            ->setDestinationOrigin($request)
            ->setParams($params);
    }
}
