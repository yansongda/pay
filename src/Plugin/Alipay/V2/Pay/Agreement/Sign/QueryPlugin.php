<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Sign;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/3dab71bc_alipay.user.agreement.query?pathHash=6706b504&ref=api&scene=common
 */
class QueryPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Pay][Agreement][Sign][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.user.agreement.query',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[Alipay][Pay][Agreement][Sign][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
