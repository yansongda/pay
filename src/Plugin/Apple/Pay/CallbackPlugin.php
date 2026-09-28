<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Apple\Pay;

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
use Yansongda\Pay\Traits\AppleTrait;
use Yansongda\Supports\Collection;

/**
 * @see https://developer.apple.com/documentation/appstoreservernotifications/responsebodyv2
 * @see https://developer.apple.com/documentation/appstoreservernotifications/responsebodyv2decodedpayload
 */
class CallbackPlugin implements PluginInterface
{
    use AppleTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Apple][Pay][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->init($rocket);

        $bodyString = (string) $rocket->getDestinationOrigin()->getBody();
        $body = json_decode($bodyString, true);

        if (JSON_ERROR_NONE !== json_last_error() || !is_array($body)) {
            throw new InvalidParamsException(Exception::PARAMS_APPLE_TOKEN_INVALID, '参数异常: Apple 回调 body 不是合法 JSON');
        }

        $signedPayload = $body['signedPayload'] ?? '';

        if ('' === $signedPayload) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: Apple 回调缺少 signedPayload');
        }

        $decoded = self::verifyAppleJws((string) $signedPayload, $rocket->getParams());

        $rocket->setDirection(NoHttpRequestDirection::class)
            ->setPayload(new Collection($decoded))
            ->setDestination(new Collection($decoded));

        Logger::info('[Apple][Pay][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

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
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, '参数异常: Apple 回调参数不正确');
        }

        $rocket->setDestination(clone $request)
            ->setDestinationOrigin($request)
            ->setParams($params);
    }
}
