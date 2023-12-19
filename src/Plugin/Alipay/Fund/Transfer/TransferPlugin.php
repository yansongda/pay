<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund\Transfer;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/62987723_alipay.fund.trans.uni.transfer?pathHash=66064890&ref=api&scene=ca56bca529e64125a2786703c6192d41
 */
class TransferPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][fund][transfer][TransferPlugin] 插件开始装载', ['rocket' => $rocket]);

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

        Logger::info('[alipay][fund][transfer][TransferPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
