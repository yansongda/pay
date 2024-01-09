<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/1aad1956_alipay.data.bill.ereceipt.apply?pathHash=a2527e9c&ref=api&scene=common
 */
class ApplyReceiptPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Fund][Transfer][ApplyReceiptPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.data.bill.ereceipt.apply',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Fund][Transfer][ApplyReceiptPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
