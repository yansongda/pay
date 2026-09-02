<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * 支付宝 V3 统一收单交易查询.
 *
 * 业务字段（`out_trade_no`/`trade_no` 二选一，`query_options` 等）由调用方经订单参数直接透传.
 *
 * @see https://github.com/alipay/alipay-sdk-php-all `v3/src/Api/AlipayTradeApi::queryRequest()`
 */
class QueryPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/v3/alipay/trade/query',
        ]);

        Logger::info('[Alipay][V3][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
