<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/cash-coupons/stock/refund-flow.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/cash-coupons/stock/refund-flow.html
 */
class QueryStockRefundFlowPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Marketing][Coupon][QueryStockRefundFlowPlugin] 插件开始装载', ['rocket' => $rocket]);

        $stockId = $rocket->getPayload()?->get('stock_id') ?? null;

        if (empty($stockId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 下载批次退款明细，参数缺少 `stock_id`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/marketing/favor/stocks/'.$stockId.'/refund-flow',
            '_service_url' => 'v3/marketing/favor/stocks/'.$stockId.'/refund-flow',
        ]);

        Logger::info('[Wechat][V3][Marketing][Coupon][QueryStockRefundFlowPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
