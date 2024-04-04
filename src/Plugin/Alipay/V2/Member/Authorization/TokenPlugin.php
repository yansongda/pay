<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2\Member\Authorization;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://opendocs.alipay.com/open/84bc7352_alipay.system.oauth.token?pathHash=fe1502d5&ref=api&scene=common
 */
class TokenPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Member][Authorization][TokenPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload(array_merge(
            [
                'method' => 'alipay.system.oauth.token',
            ],
            $rocket->getParams(),
        ));

        Logger::info('[Alipay][Member][Authorization][TokenPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
