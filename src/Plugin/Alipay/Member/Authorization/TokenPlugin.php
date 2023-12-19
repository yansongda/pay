<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Member\Authorization;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/84bc7352_alipay.system.oauth.token?pathHash=fe1502d5&ref=api&scene=common
 */
class TokenPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][member][authorization][TokenPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.system.oauth.token',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][member][authorization][TokenPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
