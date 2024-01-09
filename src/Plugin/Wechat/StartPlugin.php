<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

class StartPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][StartPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload($rocket->getParams());

        Logger::info('[Wechat][StartPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
