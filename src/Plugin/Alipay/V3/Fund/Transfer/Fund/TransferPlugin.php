<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3\Fund\Transfer\Fund;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open-v3/02a67f
 */
class TransferPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][Fund][Transfer][Fund][TransferPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/v3/alipay/fund/trans/uni/transfer',
            '_body' => array_merge(
                [
                    'biz_scene' => 'DIRECT_TRANSFER',
                    'product_code' => 'TRANS_ACCOUNT_NO_PWD',
                ],
                $rocket->getParams(),
            ),
        ]);

        Logger::info('[Alipay][V3][Fund][Transfer][Fund][TransferPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
