<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Pay\Bill;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

use function Yansongda\Artful\filter_params;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/bill-download/trade-bill/get-trade-bill.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/bill-download/trade-bill/get-trade-bill.html
 */
class GetTradePlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Pay][Bill][GetTradePlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: App 申请交易账单，参数为空');
        }

        $query = filter_params($payload)->query();

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/bill/tradebill?'.$query,
            '_service_url' => 'v3/bill/tradebill?'.$query,
        ]);

        Logger::info('[Wechat][V3][Pay][Bill][GetTradePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
