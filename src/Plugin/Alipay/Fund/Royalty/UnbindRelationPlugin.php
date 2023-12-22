<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund\Royalty;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/3613f4e1_alipay.trade.royalty.relation.unbind?pathHash=5a880175&ref=api&scene=common
 */
class UnbindRelationPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Fund][Royalty][UnbindRelationPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.trade.royalty.relation.unbind',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Fund][Royalty][UnbindRelationPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
