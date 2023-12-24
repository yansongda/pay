<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Jsapi;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/jsapi-payment/get-fund-bill.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-jsapi-payment/get-fund-bill.html
 */
class GetFundBillPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][Jsapi][GetFundBillPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        if (is_null($payload?->get('bill_date') ?? null)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Jsapi 下载交易对账单，参数缺少 `bill_date`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/bill/fundflowbill?'.$payload->query(),
            '_service_url' => 'v3/bill/fundflowbill?'.$payload->query(),
        ]);

        Logger::info('[Wechat][Pay][Jsapi][GetFundBillPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
