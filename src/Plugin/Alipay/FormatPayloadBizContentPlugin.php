<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\filter_params;

class FormatPayloadBizContentPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][FormatPayloadBizContentPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        $rocket->setPayload(new Collection(array_merge(
            filter_params($payload->all(), fn ($k, $v) => '' !== $v && 'sign' != $k),
            ['biz_content' => json_encode(filter_params($payload->get('biz_content', [])))]
        )));

        Logger::info('[Alipay][FormatPayloadBizContentPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
