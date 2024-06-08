<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use JetBrains\PhpStorm\Deprecated;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

#[Deprecated(reason: '自 v3.7.5 版本已废弃', replacement: '`yansongda/artful` 包中的 `Yansongda\Artful\Plugin\StartPlugin`')]
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
