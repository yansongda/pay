<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Extend\Complaints;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/consumer-complaint/complaint-notifications/update-complaint-notifications.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/consumer-complaint/complaint-notifications/update-complaint-notifications.html
 */
class UpdateCallbackPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Extend][Complaints][UpdateCallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        $rocket->setPayload([
            '_method' => 'PUT',
            '_url' => 'v3/merchant-service/complaint-notifications',
            '_service_url' => 'v3/merchant-service/complaint-notifications',
            'url' => $payload?->get('url', '') ?? '',
        ]);

        Logger::info('[Wechat][Extend][Complaints][UpdateCallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
