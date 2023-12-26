<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\H5;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_config_type_key;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/h5-payment/direct-jsons/h5-prepay.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-h5-payment/partner-jsons/partner-h5-prepay.html
 */
class PayPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][H5][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);

        if (Pay::MODE_SERVICE === $config['mode']) {
            $payload = $this->service($params, $config);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/pay/transactions/h5',
                '_service_url' => 'v3/pay/partner/transactions/h5',
                'notify_url' => $config['notify_url'] ?? '',
            ],
            $payload ?? $this->normal($params, $config)
        ));

        Logger::info('[Wechat][Pay][H5][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(array $params, array $config): array
    {
        return [
            'appid' => $config[get_wechat_config_type_key($params)] ?? '',
            'mchid' => $config['mch_id'] ?? '',
        ];
    }

    protected function service(array $params, array $config): array
    {
        $configKey = get_wechat_config_type_key($params);

        return [
            'sp_appid' => $config[$configKey] ?? '',
            'sp_mchid' => $config['mch_id'] ?? '',
            'sub_mchid' => $config['sub_mch_id'] ?? '',
        ];
    }
}
