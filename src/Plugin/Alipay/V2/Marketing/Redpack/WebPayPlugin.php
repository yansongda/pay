<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Marketing\Redpack;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/03rbye?pathHash=1c8d9fcb&ref=api&scene=common
 */
class WebPayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Marketing][Redpack][WebPayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->setDirection(ResponseDirection::class)
            ->mergePayload([
                'method' => 'alipay.fund.trans.page.pay',
                'biz_content' => array_merge(
                    [
                        'product_code' => 'STD_APP_TRANSFER',
                    ],
                    $rocket->getParams()
                ),
            ]);

        Logger::info('[Alipay][Marketing][Redpack][WebPayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
