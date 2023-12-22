<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund\Royalty;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/1860be54_alipay.trade.royalty.relation.batchquery?pathHash=2f733e2d&ref=api&scene=common
 */
class QueryRelationPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Fund][Royalty][QueryRelationPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.royalty.relation.batchquery',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Fund][Royalty][QueryRelationPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
