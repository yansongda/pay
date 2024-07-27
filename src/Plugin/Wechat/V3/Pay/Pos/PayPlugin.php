<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Pay\Pos;

use Closure;
use Throwable;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\get_wechat_type_key;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/code-payment-v3/direct/code-pay.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-code-payment-v3/partner/partner-code-pay.html
 */
class PayPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     * @throws Throwable                随机字符串生成失败
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Pay][Pos][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $params = $rocket->getParams();
        $config = get_provider_config('wechat', $params);

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 付款码支付，参数为空');
        }

        if (Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            $data = $this->service($payload, $params, $config);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/pay/transactions/codepay',
                '_service_url' => 'v3/pay/partner/transactions/codepay',
            ],
            $data ?? $this->normal($params, $config)
        ));

        Logger::info('[Wechat][V3][Pay][Pos][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(array $params, array $config): array
    {
        return [
            'appid' => $config[get_wechat_type_key($params)] ?? '',
            'mchid' => $config['mch_id'] ?? '',
        ];
    }

    protected function service(Collection $payload, array $params, array $config): array
    {
        $configKey = get_wechat_type_key($params);

        return [
            'sp_appid' => $config[$configKey] ?? '',
            'sp_mchid' => $config['mch_id'] ?? '',
            'sub_mchid' => $payload->get('sub_mchid', $config['sub_mch_id'] ?? ''),
        ];
    }
}
