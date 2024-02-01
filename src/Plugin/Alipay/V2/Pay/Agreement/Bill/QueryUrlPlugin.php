<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Bill;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/1523f88d_alipay.data.dataservice.bill.downloadurl.query?pathHash=6a78e19b&ref=api&scene=common
 */
class QueryUrlPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Pay][Agreement][Bill][QueryUrlPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.data.dataservice.bill.downloadurl.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Pay][Agreement][Bill][QueryUrlPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
