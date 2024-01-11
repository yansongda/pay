<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/combine-payment/orders/query-order.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/combine-payment/orders/query-order.html
 */
class QueryPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][Combine][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $combineOutTradeNo = $rocket->getPayload()?->get('combine_out_trade_no') ?? null;

        if (empty($combineOutTradeNo)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 合单查询，参数缺少 `combine_out_trade_no`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/combine-transactions/out-trade-no/'.$combineOutTradeNo,
            '_service_url' => 'v3/combine-transactions/out-trade-no/'.$combineOutTradeNo,
        ]);

        Logger::info('[Wechat][Pay][Combine][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
