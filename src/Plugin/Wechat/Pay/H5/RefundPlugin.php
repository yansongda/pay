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

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/h5-payment/create.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-h5-payment/create.html
 */
class RefundPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][H5][RefundPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);

        if (Pay::MODE_SERVICE === $config['mode']) {
            $payload = $this->service($config);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/refund/domestic/refunds',
                '_service_url' => 'v3/refund/domestic/refunds',
                'notify_url' => $config['notify_url'] ?? null,
            ],
            $payload ?? $this->normal()
        ));

        Logger::info('[Wechat][Pay][H5][RefundPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(): array
    {
        return [];
    }

    protected function service(array $config): array
    {
        return [
            'sub_mchid' => $config['sub_mch_id'] ?? '',
        ];
    }
}
