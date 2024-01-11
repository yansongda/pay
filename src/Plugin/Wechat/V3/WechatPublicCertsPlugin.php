<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

class WechatPublicCertsPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][WechatPublicCertsPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/certificates',
        ]);

        Logger::info('[Wechat][V3][WechatPublicCertsPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
