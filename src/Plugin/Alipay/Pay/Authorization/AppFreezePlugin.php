<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Pay\Authorization;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/064jhe?pathHash=629fa9a5&ref=api&scene=2a9ad7e9012248b0acd5edd04c9f31b6
 */
class AppFreezePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][pay][authorization][AppFreezePlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.fund.auth.order.app.freeze',
            'biz_content' => array_merge(
                [
                    'product_code' => 'PREAUTH_PAY',
                ],
                $rocket->getParams()
            ),
        ]);

        Logger::info('[alipay][pay][authorization][AppFreezePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
