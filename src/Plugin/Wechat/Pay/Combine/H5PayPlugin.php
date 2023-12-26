<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Combine;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_config_type_key;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/combine-payment/orders/h5-prepay.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/combine-payment/orders/h5-prepay.html
 */
class H5PayPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][Combine][H5PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/combine-transactions/h5',
                '_service_url' => 'v3/combine-transactions/h5',
                'notify_url' => $config['notify_url'] ?? '',
            ],
            $this->normal($params, $config)
        ));

        Logger::info('[Wechat][Pay][Combine][H5PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(array $params, array $config): array
    {
        return [
            'combine_appid' => $config[get_wechat_config_type_key($params)] ?? '',
            'combine_mchid' => $config['mch_id'] ?? '',
        ];
    }
}
