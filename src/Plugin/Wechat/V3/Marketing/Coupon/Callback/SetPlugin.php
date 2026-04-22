<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Callback;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Traits\WechatTrait;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/cash-coupons/call-back-url/set-callback.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/cash-coupons/call-back-url/set-callback.html
 */
class SetPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Marketing][Coupon][Callback][SetPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);
        $payload = $rocket->getPayload();

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/marketing/favor/callbacks',
                '_service_url' => 'v3/marketing/favor/callbacks',
                'mchid' => $payload?->get('mchid') ?? $config->getMchId(),
                'notify_url' => $payload?->get('notify_url') ?? $config->getNotifyUrl(),
            ],
        ));

        Logger::info('[Wechat][V3][Marketing][Coupon][Callback][SetPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
