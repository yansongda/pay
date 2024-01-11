<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

use function Yansongda\Artful\filter_params;

class FormatPayloadBizContentPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][FormatPayloadBizContentPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        $rocket->setPayload(
            filter_params($payload->all(), fn ($k, $v) => '' !== $v && 'sign' != $k)
                ->merge([
                    'biz_content' => json_encode(filter_params($payload->get('biz_content', []))),
                ])
        );

        Logger::info('[Alipay][FormatPayloadBizContentPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
