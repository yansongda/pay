<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\filter_params;

class StartPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][StartPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload(filter_params($rocket->getParams()));

        Logger::info('[Wechat][StartPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
