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
 * @see https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/industry/virtual-payment.html
 */
class VerifySignaturePlugin implements PluginInterface
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

        Logger::debug('[Wechat][Virtual][VerifySignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

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

        Logger::info('[Wechat][Virtual][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
