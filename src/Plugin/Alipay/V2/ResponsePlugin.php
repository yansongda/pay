<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V2;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\should_do_http_request;

class ResponsePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Alipay][ResponsePlugin] 插件开始装载', ['rocket' => $rocket]);

        $destination = $rocket->getDestination();
        $payload = $rocket->getPayload();
        $resultKey = str_replace('.', '_', $payload->get('method')).'_response';

        if (should_do_http_request($rocket->getDirection()) && $destination instanceof Collection) {
            $rocket->setDestination(new Collection(array_merge(
                ['_sign' => $destination->get('sign', '')],
                $destination->get($resultKey, $destination->all())
            )));
        }

        Logger::info('[Alipay][ResponsePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
