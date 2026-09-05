<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Douyin\V1\Refund;

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
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\DouyinTrait;
use Yansongda\Supports\Collection;

class CallbackPlugin implements PluginInterface
{
    use DouyinTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Douyin][V1][Refund][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $request = $params['_request'] ?? null;

        if (!$request instanceof ServerRequestInterface) {
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, '参数异常: 抖音回调参数不正确');
        }

        /** @var DouyinConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_DOUYIN, $params);

        self::verifyDouyinTradeSign($request, $config);

        $body = json_decode((string) $request->getBody(), true);

        if (!is_array($body) || 'refund' !== ($body['type'] ?? null)) {
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, '参数异常: 抖音退款结果回调类型不正确');
        }

        $msg = is_string($body['msg'] ?? null) ? json_decode($body['msg'], true) : null;

        if (!is_array($msg)) {
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, '参数异常: 抖音退款结果回调内容解析失败');
        }

        $rocket->setPayload(new Collection($msg))
            ->setDirection(NoHttpRequestDirection::class)
            ->setDestination($rocket->getPayload());

        Logger::info('[Douyin][V1][Refund][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
