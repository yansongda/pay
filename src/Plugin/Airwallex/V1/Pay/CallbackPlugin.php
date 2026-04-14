<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Airwallex\V1\Pay;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Airwallex\V1\VerifyWebhookSignPlugin;
use Yansongda\Supports\Collection;

class CallbackPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Airwallex][V1][Pay][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->init($rocket);

        (new VerifyWebhookSignPlugin())->assembly($rocket, static fn (Rocket $rocket): Rocket => $rocket);

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
