<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Openapi;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

use function Yansongda\Artful\should_do_http_request;

/**
 * 校验微信开放接口（api.weixin.qq.com）响应：HTTP 状态码 + 业务 errcode.
 *
 * 注意：微信开放接口响应不在 HTTP Header 中携带签名，
 * 因此此插件仅校验 HTTP 状态码与 errcode。
 */
class ResponsePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidResponseException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Wechat][Openapi][ResponsePlugin] 插件开始装载', ['rocket' => $rocket]);

        if (!should_do_http_request($rocket->getDirection()) || is_null($rocket->getDestinationOrigin())) {
            return $rocket;
        }

        $response = $rocket->getDestinationOrigin();

        if ($response instanceof ResponseInterface
            && ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300)) {
            throw new InvalidResponseException(Exception::RESPONSE_CODE_WRONG, '微信开放接口返回状态码异常，请检查参数是否错误', $rocket->getDestination());
        }

        $destination = $rocket->getDestination();

        if (!is_null($destination)) {
            $errcode = $destination->get('errcode');

            if (null !== $errcode && 0 !== $errcode) {
                throw new InvalidResponseException(
                    Exception::RESPONSE_BUSINESS_CODE_WRONG,
                    '微信开放接口返回业务异常: '.$destination->get('errmsg'),
                    $destination,
                );
            }
        }

        Logger::info('[Wechat][Openapi][ResponsePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
