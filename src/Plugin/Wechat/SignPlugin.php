<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Str;

class SignPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[wechat][SignPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->filterPayload($rocket);

        // todo: 2、进行签名
        // todo: 3、把签名等信息增加到 radar 里 request 的 header 中

        Logger::info('[wechat][SignPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function filterPayload(Rocket $rocket): void
    {
        $payload = $rocket->getPayload()->filter(function ($v, $k) {
            return !Str::startsWith(strval($k), '_');
        });

        $rocket->setPayload($payload);
    }
}
