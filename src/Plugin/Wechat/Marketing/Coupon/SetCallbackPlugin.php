<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Coupon;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/cash-coupons/call-back-url/set-callback.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/cash-coupons/call-back-url/set-callback.html
 */
class SetCallbackPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Coupon][SetCallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/marketing/favor/callbacks',
                '_service_url' => 'v3/marketing/favor/callbacks',
                'mchid' => $config['mch_id'],
                'notify_url' => $config['notify_url'] ?? '',
            ],
        ));

        Logger::info('[Wechat][Marketing][Coupon][SetCallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
