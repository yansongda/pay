<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Web;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/e099d91f_alipay.data.dataservice.bill.downloadurl.query?pathHash=2319d4ee&ref=api&scene=common
 */
class QueryBillUrlPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][pay][web][QueryBillUrlPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.data.dataservice.bill.downloadurl.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][pay][web][QueryBillUrlPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
