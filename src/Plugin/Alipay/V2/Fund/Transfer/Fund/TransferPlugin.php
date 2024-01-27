<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\Fund;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/62987723_alipay.fund.trans.uni.transfer?pathHash=66064890&ref=api&scene=ca56bca529e64125a2786703c6192d41
 * @see https://opendocs.alipay.com/open/02byvi?pathHash=b367173b&ref=api&scene=66dd06f5a923403393b85de68d3c0055
 */
class TransferPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Fund][Transfer][Fund][TransferPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.fund.trans.uni.transfer',
            'biz_content' => array_merge(
                [
                    'biz_scene' => 'DIRECT_TRANSFER',
                    'product_code' => 'TRANS_ACCOUNT_NO_PWD',
                ],
                $rocket->getParams(),
            ),
        ]);

        Logger::info('[Alipay][Fund][Transfer][Fund][TransferPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
