<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Douyin\V1\Refund;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\ProviderConfigTrait;

use function Yansongda\Artful\filter_params;

/**
 * 创建退款：透传 order_id/out_refund_no/refund_reason 等业务字段，业务字段均无 app_id，不注入.
 */
class RefundPlugin implements PluginInterface
{
    use ProviderConfigTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Douyin][V1][Refund][RefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        if (is_null($payload) || filter_params($payload)->isEmpty()) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 抖音创建退款，缺少必要的业务参数');
        }

        /** @var DouyinConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_DOUYIN, $rocket->getParams());

        $merge = [
            '_method' => 'POST',
            '_url' => '/api/trade_basic/v1/developer/refund_create/',
        ];

        $refundNotifyUrl = $config->getRefundNotifyUrl();

        if (!$payload->has('notify_url') && !empty($refundNotifyUrl)) {
            $merge['notify_url'] = $refundNotifyUrl;
        }

        $rocket->mergePayload($merge);

        Logger::info('[Douyin][V1][Refund][RefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
