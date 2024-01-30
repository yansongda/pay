<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Auth;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/09bj50?pathHash=9c577eb9&ref=api&scene=b58198cdbff342edad810bfee704489a
 */
class ScanFreezePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Pay][Authorization][Auth][ScanFreezePlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.fund.auth.order.voucher.create',
            'biz_content' => array_merge(
                [
                    'product_code' => 'PREAUTH_PAY',
                ],
                $rocket->getParams()
            ),
        ]);

        Logger::info('[Alipay][Pay][Authorization][Auth][ScanFreezePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
