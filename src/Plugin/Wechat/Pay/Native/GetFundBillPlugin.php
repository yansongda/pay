<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Native;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/native-payment/get-fund-bill.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-native-payment/get-fund-bill.html
 */
class GetFundBillPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][Native][GetFundBillPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/bill/fundflowbill?'.$payload->query(),
            '_service_url' => 'v3/bill/fundflowbill?'.$payload->query(),
        ]);

        Logger::info('[Wechat][Pay][Native][GetFundBillPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
