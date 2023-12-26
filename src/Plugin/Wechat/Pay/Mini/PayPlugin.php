<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Mini;

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
 * @see https://pay.weixin.qq.com/docs/merchant/apis/mini-program-payment/mini-prepay.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-mini-program-payment/partner-mini-prepay.html
 */
class PayPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][Mini][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);

        if (Pay::MODE_SERVICE === $config['mode']) {
            $payload = $this->service($params, $config);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/pay/transactions/jsapi',
                '_service_url' => 'v3/pay/partner/transactions/jsapi',
                'notify_url' => $config['notify_url'] ?? '',
            ],
            $payload ?? $this->normal($params, $config)
        ));

        Logger::info('[Wechat][Pay][Mini][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

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

        $payload = [
            'sub_mchid' => $config['sub_mch_id'] ?? '',
            'sp_appid' => $config[$configKey] ?? '',
            'sp_mchid' => $config['mch_id'] ?? '',
        ];

        if (!empty($params['payer']['sub_openid'])) {
            $payload['sub_appid'] = $config['sub_'.$configKey] ?? '';
        }

        return $payload;
    }
}
