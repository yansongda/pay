<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Fund\PCreditPayInstallment;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/02np8z?pathHash=994a1e7d&ref=api
 */
class AppPayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Fund][PCreditPayInstallment][AppPayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->setDirection(ResponseDirection::class)
            ->mergePayload([
                'method' => 'alipay.trade.app.pay',
                'biz_content' => $rocket->getParams(),
            ]);

        Logger::info('[Alipay][Fund][PCreditPayInstallment][AppPayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
