<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Marketing\Redpack;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/03rbyf?pathHash=76643847&ref=api&scene=common
 */
class AppPayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Marketing][Redpack][AppPayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->setDirection(ResponseDirection::class)
            ->mergePayload([
                'method' => 'alipay.fund.trans.app.pay',
                'biz_content' => array_merge(
                    [
                        'product_code' => 'STD_RED_PACKET',
                        'biz_scene' => 'PERSONAL_PAY',
                    ],
                    $rocket->getParams(),
                ),
            ]);

        Logger::info('[Alipay][Marketing][Redpack][AppPayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
