<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund\Transfer;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/76606b11_alipay.data.dataservice.bill.downloadurl.query?pathHash=425f62c6&ref=api&scene=common
 */
class QueryBillUrlPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Fund][Transfer][QueryBillUrlPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.data.dataservice.bill.downloadurl.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Fund][Transfer][QueryBillUrlPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
