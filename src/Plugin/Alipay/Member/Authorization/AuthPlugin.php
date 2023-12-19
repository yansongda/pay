<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Member\Authorization;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://opendocs.alipay.com/open/02aile?pathHash=4efd837f&ref=api&scene=common
 */
class AuthPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[alipay][member][authorization][AuthPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.user.info.auth',
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][member][authorization][AuthPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
