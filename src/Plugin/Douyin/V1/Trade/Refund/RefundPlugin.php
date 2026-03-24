<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Douyin\V1\Trade\Refund;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

use function Yansongda\Artful\filter_params;
use function Yansongda\Pay\get_douyin_trade_sign;
use function Yansongda\Pay\get_provider_config;

/**
 * @see https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/refund/create-refund
 */
class RefundPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Douyin][V1][Trade][Refund][RefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $params = $rocket->getParams();
        $config = get_provider_config('douyin', $params);

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 抖音交易系统-退款，参数为空');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => 'api/trade/v1/create_refund',
            'app_id' => $config['app_id'] ?? '',
            'notify_url' => $payload->get('notify_url') ?? $config['refund_notify_url'] ?? $config['notify_url'] ?? '',
        ]);

        $sign = get_douyin_trade_sign($config, filter_params($rocket->getPayload()));

        $rocket->mergePayload(['sign' => $sign]);

        Logger::info('[Douyin][V1][Trade][Refund][RefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
