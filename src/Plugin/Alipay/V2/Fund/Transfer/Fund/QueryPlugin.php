<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\Fund;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/58a29899_alipay.fund.trans.common.query?pathHash=aad07c6d&ref=api&scene=f9fece54d41f49cbbd00dc73655a01a4
 */
class QueryPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Fund][Transfer][Fund][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.fund.trans.common.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Fund][Transfer][Fund][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
