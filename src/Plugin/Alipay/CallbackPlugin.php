<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\AlipayTrait;

use function Yansongda\Artful\filter_params;

class CallbackPlugin implements PluginInterface
{
    use AlipayTrait;

    /**
     * 支付宝异步通知（V2 form 参数格式，V2/V3 接口的报文与验签组串同构）：
     * 除去 `sign`/`sign_type` 后按字典序组串 + RSA2 验签；不校验 SN、无时间戳环节、无条件强制.
     *
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     * @throws InvalidSignException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var AlipayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_ALIPAY, $params);

        $value = filter_params($params, fn ($k, $v) => '' !== $v && 'sign' != $k && 'sign_type' != $k);

        self::verifyAlipaySign($config, $value->sortKeys()->toString(), $params['sign'] ?? '');

        $rocket->setPayload($params)
            ->setDirection(NoHttpRequestDirection::class)
            ->setDestination($rocket->getPayload());

        Logger::info('[Alipay][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
