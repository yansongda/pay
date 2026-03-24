<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Douyin\V1\Trade\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;

use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\verify_douyin_trade_sign;

/**
 * @see https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/order/notify-payment-result
 */
class CallbackPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Douyin][V1][Trade][Pay][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_provider_config('douyin', $params);

        verify_douyin_trade_sign($config, $params);

        $rocket->setPayload($params)
            ->setDirection(NoHttpRequestDirection::class)
            ->setDestination($rocket->getPayload());

        Logger::info('[Douyin][V1][Trade][Pay][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
