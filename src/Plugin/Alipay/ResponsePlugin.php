<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;
use function Yansongda\Pay\should_do_http_request;

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
            $rocket->setDestination(Collection::wrap($destination->get($resultKey, [])));
        }

        Logger::info('[Alipay][ResponsePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
