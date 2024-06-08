<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Extend\ProfitSharing;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

use function Yansongda\Pay\get_provider_config;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/orders/query-order.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/profit-sharing/orders/query-order.html
 */
class QueryPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Extend][ProfitSharing][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $config = get_provider_config('wechat', $rocket->getParams());
        $payload = $rocket->getPayload();
        $outOrderNo = $payload?->get('out_order_no') ?? null;
        $transactionId = $payload?->get('transaction_id') ?? null;
        $subMchId = $payload?->get('sub_mchid') ?? $config['sub_mch_id'] ?? 'null';

        if (empty($outOrderNo) || empty($transactionId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 查询分账结果, 缺少必要参数 `out_order_no`, `transaction_id`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/profitsharing/orders/'.$outOrderNo.'?transaction_id='.$transactionId,
            '_service_url' => 'v3/profitsharing/orders/'.$outOrderNo.'?sub_mchid='.$subMchId.'&transaction_id='.$transactionId,
        ]);

        Logger::info('[Wechat][Extend][ProfitSharing][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
