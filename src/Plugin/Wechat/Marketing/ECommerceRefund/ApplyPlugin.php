<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\ECommerceRefund;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/docs/partner/apis/ecommerce-refund/refunds/create-refund.html
 */
class ApplyPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][ECommerceRefund][ApplyPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();
        $config = get_wechat_config($params);

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 平台收付通（退款）-申请退款，缺少必要参数');
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_service_url' => 'v3/ecommerce/refunds/apply',
            ],
            $this->service($payload, $config)
        ));

        Logger::info('[Wechat][Marketing][ECommerceRefund][ApplyPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function service(Collection $payload, array $config): array
    {
        return [
            'sub_mchid' => $payload->get('sub_mchid', $config['sub_mch_id']),
            'sp_appid' => $payload->get('sp_appid', $config['app_id']),
            'notify_url' => $payload->get('notify_url', $config['notify_url'] ?? null),
        ];
    }
}