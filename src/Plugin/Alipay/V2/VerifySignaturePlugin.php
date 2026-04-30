<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Traits\AlipayTrait;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\should_do_http_request;

class VerifySignaturePlugin implements PluginInterface
{
    use AlipayTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     * @throws InvalidSignException
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Alipay][VerifySignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        if (!should_do_http_request($rocket->getDirection())) {
            return $rocket;
        }

        $destination = $rocket->getDestination();

        if ((!$destination instanceof Collection) || empty($result = $destination->except('_sign')->all())) {
            throw new InvalidParamsException(Exception::RESPONSE_EMPTY, '参数异常: 支付宝验证签名时待验签参数不正确', $destination);
        }

        /** @var AlipayConfig $config */
        $config = self::getProviderConfig('alipay', $rocket->getParams());

        self::verifyAlipaySign($config, json_encode($result, JSON_UNESCAPED_UNICODE), $destination->get('_sign', ''));

        Logger::info('[Alipay][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
