<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Mini;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/mini-program-payment/get-trade-bill.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-mini-program-payment/get-trade-bill.html
 */
class GetTradeBillPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][Mini][GetTradeBillPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/bill/tradebill?'.$payload->query(),
            '_service_url' => 'v3/bill/tradebill?'.$payload->query(),
        ]);

        Logger::info('[Wechat][Pay][Mini][GetTradeBillPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
