<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/fapiao/fapiao-merchant/update-development-config.html
 */
class UpdateConfigPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Marketing][Fapiao][UpdateConfigPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_method' => 'PATCH',
            '_url' => 'v3/new-tax-control-fapiao/merchant/development-config',
        ]);

        Logger::info('[Wechat][V3][Marketing][Fapiao][UpdateConfigPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
