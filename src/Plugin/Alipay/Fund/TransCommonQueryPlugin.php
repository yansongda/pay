<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/02byve
 */
class TransCommonQueryPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[alipay][TransCommonQueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.fund.trans.common.query',
            'biz_content' => array_merge(
                [
                    'product_code' => 'TRANS_ACCOUNT_NO_PWD',
                    'biz_scene' => 'DIRECT_TRANSFER',
                ],
                $rocket->getParams(),
            ),
        ]);

        Logger::info('[alipay][TransCommonQueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
