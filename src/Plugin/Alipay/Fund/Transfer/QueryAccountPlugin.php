<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund\Transfer;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/1b8a680c_alipay.fund.account.query?pathHash=ff8fc0e0&ref=api&scene=c76aa8f1c54e4b8b8ffecfafc4d3c31c
 */
class QueryAccountPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][fund][transfer][QueryAccountPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.fund.account.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][fund][transfer][QueryAccountPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}