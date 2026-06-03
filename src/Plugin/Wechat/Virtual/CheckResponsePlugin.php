<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Virtual;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

use function Yansongda\Artful\should_do_http_request;

/**
 * 校验微信虚拟支付响应的业务错误码.
 *
 * 注意：微信虚拟支付服务端 API 的响应不在 HTTP Header 中携带签名，
 * 因此此插件仅校验 errcode 而非签名。
 *
 * @see https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/virtual-payment.html#_2-5-%E7%AD%BE%E5%90%8D%E8%AF%A6%E8%A7%A3
 */
class CheckResponsePlugin implements PluginInterface
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

        Logger::debug('[Wechat][Virtual][CheckResponsePlugin] 插件开始装载', ['rocket' => $rocket]);

        if (!should_do_http_request($rocket->getDirection()) || is_null($rocket->getDestinationOrigin())) {
            return $rocket;
        }

        $destination = $rocket->getDestination();

        if (!is_null($destination) && 0 !== $destination->get('errcode')) {
            throw new InvalidResponseException(
                Exception::RESPONSE_BUSINESS_CODE_WRONG,
                '微信虚拟支付返回业务异常: '.$destination->get('errmsg'),
                $destination,
            );
        }

        Logger::info('[Wechat][Virtual][CheckResponsePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
