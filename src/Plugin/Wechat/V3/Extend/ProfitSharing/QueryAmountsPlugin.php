<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Extend\ProfitSharing;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/transactions/query-order-amount.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/profit-sharing/transactions/query-order-amount.html
 */
class QueryAmountsPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Extend][ProfitSharing][QueryAmountsPlugin] 插件开始装载', ['rocket' => $rocket]);

        $transactionId = $rocket->getPayload()?->get('transaction_id');

        if (is_null($transactionId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 查询剩余待分金额，参数缺少 `transaction_id`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/profitsharing/transactions/'.$transactionId.'/amounts',
            '_service_url' => 'v3/profitsharing/transactions/'.$transactionId.'/amounts',
        ]);

        Logger::info('[Wechat][Extend][ProfitSharing][QueryAmountsPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
