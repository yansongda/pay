<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Apple\Pay;

use Closure;
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

/**
 * Apple Pay 支付令牌本地验签解密插件（不发 HTTP 请求）。
 *
 * 从 params 取 `token`/`_token`，经 `verifyAppleToken()` 验签解密，
 * 将解密结果直接作为 payload 与 destination（Collection）返回给调用方。
 */
class PayTokenPlugin implements PluginInterface
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
        Logger::debug('[Apple][Pay][PayTokenPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $token = $params['token'] ?? $params['_token'] ?? null;

        if (empty($token)) {
            throw new InvalidParamsException(Exception::PARAMS_APPLE_TOKEN_INVALID, '参数异常: Apple 支付令牌缺失 -- [token]');
        }

        $result = self::verifyAppleToken($token, $params);

        $rocket->setDirection(NoHttpRequestDirection::class)
            ->setPayload($result)
            ->setDestination($result);

        Logger::info('[Apple][Pay][PayTokenPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
