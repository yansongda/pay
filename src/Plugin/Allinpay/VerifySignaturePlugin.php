<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Allinpay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AllinpayConfig;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\AllinpayTrait;

use function Yansongda\Artful\should_do_http_request;

/**
 * @see https://prodoc.allinpay.com/doc/2318/ 通联支付接口安全规范 - 响应验签（除 sign 外所有非空字段排序组装后以通联公钥验签）
 */
class VerifySignaturePlugin implements PluginInterface
{
    use AllinpayTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::info('[Allinpay][VerifySignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        if (should_do_http_request($rocket->getDirection())) {
            $params = $rocket->getParams();

            /** @var AllinpayConfig $config */
            $config = self::getProviderConfig(Pay::PROVIDER_ALLINPAY, $params);

            self::verifyAllinpaySign($config, $rocket->getDestination());
        }

        Logger::info('[Allinpay][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
