<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3\Pay\Authorization\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://github.com/alipay/alipay-sdk-php-all/blob/master/v3/docs/Api/AlipayTradeOrderinfoApi.md
 */
class SyncPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][Pay][Authorization][Pay][SyncPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/v3/alipay/trade/orderinfo/sync',
            '_body' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][V3][Pay][Authorization][Pay][SyncPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
