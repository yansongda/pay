<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

class WechatPublicCertsPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][WechatPublicCertsPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/certificates',
        ]);

        Logger::info('[Wechat][WechatPublicCertsPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
