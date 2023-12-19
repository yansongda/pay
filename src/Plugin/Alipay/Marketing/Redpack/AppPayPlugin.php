<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Marketing\Redpack;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/03rbyf?pathHash=76643847&ref=api&scene=common
 */
class AppPayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][marketing][redpack][AppPayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.app.pay',
            'biz_content' => array_merge(
                [
                    'product_code' => 'STD_RED_PACKET',
                    'biz_scene' => 'PERSONAL_PAY',
                ],
                $rocket->getParams(),
            ),
        ]);

        Logger::info('[alipay][marketing][redpack][AppPayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
