<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Str;

class FormatBizContentPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][FormatBizContentPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload()->filter(fn ($v, $k) => '' !== $v && !is_null($v) && 'sign' != $k);

        $contents = array_filter($payload->get('biz_content', []), fn ($v, $k) => !Str::startsWith(strval($k), '_'), ARRAY_FILTER_USE_BOTH);

        $rocket->setPayload(
            $payload->merge(['biz_content' => json_encode($contents)])
        );

        Logger::info('[Alipay][FormatBizContentPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
